<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikeIsAlreadyBeenCreated;
use GeekBrains\LevelTwo\Blog\Exceptions\LikeNotFoundException;
use GeekBrains\LevelTwo\Blog\Like;
use GeekBrains\LevelTwo\Blog\Name;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use \PDO;
use Psr\Log\LoggerInterface;

class SqliteLikesPostRepository implements LikesPostRepositoryInterface
{
    public function __construct(
        private PDO $connection,
        private LoggerInterface $logger
    ){
    }

    public function save(Like $like): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO likes (uuid, uuid_post, uuid_user)
                VALUES (:uuid, :uuid_post, :uuid_user)'
        );
        $uuid = $like->getUuid();
        $statement->execute([
            'uuid' => $uuid,
            'uuid_post' => $like->getPost()->getUuid(),
            'uuid_user' => $like->getUser()->getUuid()
        ]);
        $this->logger->info("Like saved: $uuid");
    }

    /**
     * @throws LikeNotFoundException
     * @throws InvalidArgumentException
     */
    public function getByPostUuid(UUID $uuid_post): array
    {
        $statement = $this->connection->prepare(
            'SELECT likes.uuid AS likes_uuid,
                    likes.uuid_post,
                    likes.uuid_user,
                    posts.author__uuid,
                    posts.title,
                    posts.text AS post_text,
                    users.uuid AS user_uuid,
                    users.login, 
                    users.first_name, 
                    users.last_name,
                    users.password
            FROM likes
            JOIN posts ON posts.uuid = likes.uuid_post
            JOIN users ON users.uuid = likes.uuid_user
            WHERE likes.uuid_post = :uuid_post'
        );
        $statement->execute([
            'uuid_post' => (string) $uuid_post
        ]);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        $statementForUserPost = $this->connection->prepare(
            'SELECT users.uuid AS user_uuid_post, 
                    users.login, 
                    users.first_name, 
                    users.last_name,
                    users.password
            FROM users
            JOIN posts ON users.uuid = posts.author__uuid
            WHERE posts.uuid = :uuid_post'
        );
        $statementForUserPost->execute([
            'uuid_post' => (string) $uuid_post
        ]);
        $resultForUserPost = $statementForUserPost->fetch(PDO::FETCH_ASSOC);

        $author = new User (
            new UUID($resultForUserPost['user_uuid_post']),
            new Name(
                $resultForUserPost['first_name'],
                $resultForUserPost['last_name'],
            ),
            $resultForUserPost['login'],
            $resultForUserPost['password'],
        );


        if ($result === false || $resultForUserPost === false) {
            $this->logger->warning(
                "Cannot get likes for the post: $uuid_post"
            );
            throw new LikeNotFoundException("Cannot get likes for the post: $uuid_post");
        }
        $likesPost = [];

        foreach ($result as $value) {

            $user = new User(
                new UUID($value['uuid_user']),
                new Name(
                    $value['first_name'],
                    $value['last_name']
                ),
                $value['login'],
                $value['password']
            );
            $post = new Post(
                $uuid_post,
                $author,
                $value['title'],
                $value['post_text']
            );
            $like = new Like(
                new UUID($value['likes_uuid']),
                $post,
                $user,
            );
            $likesPost[] = $like;
        }

        return $likesPost;
    }

    /**
     * @throws LikeIsAlreadyBeenCreated
     */
    public function checkLike(string $uuid_user, string $uuid_post): bool
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes WHERE uuid_user = :uuid_user AND uuid_post = :uuid_post'
        );
        $statement->execute([
            'uuid_user' => $uuid_user,
            'uuid_post' => $uuid_post
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result !== false) {
            throw new LikeIsAlreadyBeenCreated(
                "The like has already been created!"
            );
        }

        return true;
    }
}
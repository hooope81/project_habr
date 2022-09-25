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

class SqliteLikesRepository implements LikesRepositoryInterface
{
    public function __construct(
        private PDO $connection
    ){
    }

    public function save(Like $like): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO likes (uuid, uuid_post, uuid_user)
                VALUES (:uuid, :uuid_post, :uuid_user)'
        );
        $statement->execute([
            'uuid' => $like->getUuid(),
            'uuid_post' => $like->getPost()->getUuid(),
            'uuid_user' => $like->getUser()->getUuid()
        ]);
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
                    users.last_name
            FROM likes
            JOIN posts ON posts.uuid = likes.uuid_post
            JOIN users ON users.uuid = posts.author__uuid
            WHERE likes.uuid_post = :uuid_post'
        );
        $statement->execute([
            'uuid_post' => (string) $uuid_post
        ]);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        $statementForUserLike = $this->connection->query(
            'SELECT users.uuid AS user_uuid_like, 
                    users.login AS user_login_like, 
                    users.first_name AS user_first_name_like, 
                    users.last_name AS user_last_name_like
            FROM users
            JOIN likes ON users.uuid = likes.uuid_user
            WHERE likes.uuid_post = :uuid_post'
        );
        $statementForUserLike->execute([
            'uuid_post' => (string) $uuid_post
        ]);
        $resultForUserLike = $statementForUserLike->fetchAll(PDO::FETCH_ASSOC);


        if ($result === false || $resultForUserLike === false) {
            throw new LikeNotFoundException(
                "Cannot get likes for the post: $uuid_post"
            );
        }
        $likesPost = [];

        foreach ($result as $value) {

            $userLike = '';
            foreach ($resultForUserLike as $item) {
                if ($item['user_uuid_like'] === $value['uuid_user']) {
                    $userLike = new User(
                        new UUID($item['user_uuid_like']),
                        new Name(
                            $item['user_first_name_like'],
                            $item['user_last_name_like']
                        ),
                        $item['user_login_like']
                    );
                }
            }

            $userPost = new User(
                new UUID($value['user_uuid']),
                new Name(
                    $value['first_name'],
                    $value['last_name']
                ),
                $value['login']
            );
            $post = new Post(
                $uuid_post,
                $userPost,
                $value['title'],
                $value['post_text']
            );
            $like = new Like(
                new UUID($value['likes_uuid']),
                $post,
                $userLike,
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
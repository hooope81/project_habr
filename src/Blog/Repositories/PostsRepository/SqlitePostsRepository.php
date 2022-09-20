<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\PostsRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Name;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use \PDO;

class SqlitePostsRepository implements PostsRepositoryInterface
{
    public function __construct(
        private PDO $connection
    ){
    }

    public function save(Post $post): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO posts (uuid, author__uuid, title, text) 
                VALUES (:uuid, :author__uuid, :title, :text)'
        );
        $statement->execute([
            'uuid' => $post->getUuid(),
            'author__uuid' => $post->getUser()->getUuid(),
            'title' => $post->getTitle(),
            'text' => $post->getText()
        ]);
    }

    /**
     * @throws PostNotFoundException
     * @throws InvalidArgumentException
     */
    public function get(UUID $uuid): Post
    {
        $statement = $this->connection->prepare(
            'SELECT posts.uuid AS post_uuid, posts.title, posts.text, users.uuid AS user_uuid, users.login, users.first_name, users.last_name
            FROM posts
            JOIN users ON users.uuid = posts.author__uuid
            WHERE posts.uuid = :uuid'
        );

        $statement->execute([
            'uuid' => (string) $uuid
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            throw new PostNotFoundException(
                "Cannot get post: $uuid"
            );
        }
        $user = new User(
            new UUID($result['user_uuid']),
            new Name($result['first_name'], $result['last_name']),
            $result['login']
        );

        return new Post(
            $uuid,
            $user,
            $result['title'],
            $result['text']
        );
    }

//    public function getUserForPost(string $author__uuid): User
//    {
//        $sqliteUsersRepository = new SqliteUsersRepository($this->connection);
//        return $sqliteUsersRepository->get(new UUID($author__uuid));
//    }

    public function delete(string $uuid): void {
        $statement = $this->connection->prepare(
            'DELETE FROM posts WHERE uuid = :uuid'
        );
        $statement->execute([
            'uuid' => $uuid
        ]);
    }
}


<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\PostsRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
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
     */
    public function get(UUID $uuid): Post
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM posts WHERE uuid = :uuid'
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
        return new Post(
            new UUID($result['uuid']),
            $this->getUserForPost($result['author__uuid']),
            $result['title'],
            $result['text']
        );
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function getUserForPost(string $author__uuid): User
    {
        $sqliteUsersRepository = new SqliteUsersRepository($this->connection);
        return $sqliteUsersRepository->get(new UUID($author__uuid));
    }
}


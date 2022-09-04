<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository;

use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use PDO;

class SqliteCommentsRepository implements CommentsRepositoryInterface
{
    public function __construct(
        private PDO $connection
    ){
    }

    public function save(Comment $comment): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO comments (uuid, author__uuid, post__uuid, text) 
                 VALUES (:uuid, :author__uuid, :post__uuid, :text)'
        );
        $statement->execute([
            'uuid' => $comment->getUuid(),
            'author__uuid' => $comment->getUser()->getUuid(),
            'post__uuid' => $comment->getPost()->getUuid(),
            'text' => $comment->getText()
        ]);
    }

    /**
     * @throws CommentNotFoundException
     */
    public function get(UUID $uuid): Comment
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM comments WHERE uuid = :uuid'
        );
        $statement->execute([
            'uuid' => (string) $uuid
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            throw new CommentNotFoundException(
                "Cannot get comment: $uuid"
            );
        }
        return new Comment(
            $uuid,
            $this->getUser($result['author__uuid']),
            $this->getPost($result['post__uuid']),
            $result['text']
        );
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function getUser(string $author__uuid): User
    {
        $sqliteUsersRepository = new SqliteUsersRepository($this->connection);
        return $sqliteUsersRepository->get(new UUID($author__uuid));
    }

    /**
     * @throws PostNotFoundException
     * @throws InvalidArgumentException
     */
    public function getPost(string $post__uuid): Post
    {
        $sqlitePostsRepository = new SqlitePostsRepository($this->connection);
        return $sqlitePostsRepository->get(new UUID($post__uuid));

    }
}

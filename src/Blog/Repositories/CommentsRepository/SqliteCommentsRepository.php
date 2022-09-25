<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository;

use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Name;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use PDO;

class SqliteCommentsRepository implements CommentsRepositoryInterface
{
    public function __construct(
        private PDO $connection
    )
    {
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
     * @throws InvalidArgumentException
     */
    public function get(UUID $uuid): Comment
    {
        $statement = $this->connection->prepare(
            'SELECT comments.uuid AS comment_uuid,
                    comments.text AS comment_text, 
                    posts.uuid AS post_uuid, 
                    posts.title, 
                    posts.text AS post_text,
                    users.uuid AS user_uuid, 
                    users.login, 
                    users.first_name, 
                    users.last_name
            FROM comments
            JOIN posts ON posts.uuid = comments.post__uuid
            JOIN users ON users.uuid = posts.author__uuid
            WHERE comments.uuid = :uuid'
        );
        $statement->execute([
            'uuid' => (string)$uuid
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        $statementForUserComment = $this->connection->prepare(
            'SELECT users.uuid AS user_uuid_comment, 
                    users.login AS user_login_comment, 
                    users.first_name AS user_first_name_comment, 
                    users.last_name AS user_last_name_comment
            FROM comments
            JOIN users ON users.uuid = comments.author__uuid
            WHERE comments.uuid = :uuid'
        );
        $statementForUserComment->execute([
            'uuid' => (string)$uuid
        ]);
        $resultForUserComment = $statementForUserComment->fetch(PDO::FETCH_ASSOC);


        if ($result === false || $resultForUserComment === false) {
            throw new CommentNotFoundException(
                "Cannot get comment: $uuid"
            );
        }
        $userComment = new User(
            new UUID($resultForUserComment['user_uuid_comment']),
            new Name(
                $resultForUserComment['user_first_name_comment'],
                $resultForUserComment['user_last_name_comment']),
            $resultForUserComment['user_login_comment']
        );
        $userPost = new User(
            new UUID($result['user_uuid']),
            new Name($result['first_name'], $result['last_name']),
            $result['login']
        );
        $post = new Post(
            new UUID($result['post_uuid']),
            $userPost,
            $result['title'],
            $result['post_text']
        );
        return new Comment(
            $uuid,
            $userComment,
            $post,
            $result['comment_text']
        );
    }
}
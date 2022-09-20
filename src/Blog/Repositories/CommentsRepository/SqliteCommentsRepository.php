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
     * @throws InvalidArgumentException
     */
    public function get(UUID $uuid): Comment
    {
        $statement = $this->connection->prepare(
            'SELECT comments.uuid AS comment_uuid, comments.text AS comment_text, 
            posts.uuid AS post_uuid, posts.title, posts.text AS post_text,
            users.uuid AS user_uuid, users.login, users.first_name, users.last_name
            FROM comments
            JOIN posts ON posts.uuid = comments.post__uuid
            JOIN users ON users.uuid = posts.author__uuid
            WHERE comments.uuid = :uuid'
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
        $user = new User(
            new UUID($result['user_uuid']),
            new Name($result['first_name'], $result['last_name']),
            $result['login']
        );
        $post = new Post(
            new UUID($result['post_uuid']),
            $user,
            $result['title'],
            $result['post_text']
        );
        return new Comment(
            $uuid,
            $user,
            $post,
            $result['comment_text']
        );
    }

//    /**
//     * @throws UserNotFoundException
//     * @throws InvalidArgumentException
//     */
//    public function getUser(string $author__uuid): User
//    {
//        $sqliteUsersRepository = new SqliteUsersRepository($this->connection);
//        return $sqliteUsersRepository->get(new UUID($author__uuid));
//    }
//
//    /**
//     * @throws PostNotFoundException
//     * @throws InvalidArgumentException
//     */
//    public function getPost(string $post__uuid): Post
//    {
//        $sqlitePostsRepository = new SqlitePostsRepository($this->connection);
//        return $sqlitePostsRepository->get(new UUID($post__uuid));
//
//    }
}

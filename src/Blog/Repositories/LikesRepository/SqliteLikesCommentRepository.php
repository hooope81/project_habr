<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesRepository;

use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikeCommentIsAlreadyBeenCreate;
use GeekBrains\LevelTwo\Blog\Exceptions\LikeCommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikeIsAlreadyBeenCreated;
use GeekBrains\LevelTwo\Blog\Like;
use GeekBrains\LevelTwo\Blog\LikeComment;
use GeekBrains\LevelTwo\Blog\Name;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use PDO;
use Psr\Log\LoggerInterface;

class SqliteLikesCommentRepository implements LikesCommentIRepositoryInterface
{
    public function __construct(
        private PDO $connection,
        private LoggerInterface $logger
    ){
    }

    public function save(LikeComment $likeComment): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO likesComment (uuid, uuid_comment, uuid_user)
                VALUES (:uuid, :uuid_comment, :uuid_user)'
        );
        $uuid = $likeComment->getUuid();
        $statement->execute([
            'uuid' => $uuid,
            'uuid_comment' => $likeComment->getComment()->getUuid(),
            'uuid_user' => $likeComment->getUser()->getUuid()
        ]);
        $this->logger->info("LikeComment saved: $uuid");
    }

    /**
     * @throws InvalidArgumentException
     * @throws LikeCommentNotFoundException
     */
    public function getByCommentUuid(UUID $uuid_comment): array
    {
        $statement = $this->connection->prepare(
            'SELECT likesComment.uuid AS likes_uuid,
                    likesComment.uuid_comment,
                    likesComment.uuid_user,
                    comments.uuid AS comment_uuid,
                    comments.author__uuid AS comment_user,
                    comments.post__uuid,
                    comments.text AS comment_text,
                    users.uuid AS user_uuid,
                    users.login, 
                    users.first_name, 
                    users.last_name,
                    users.password
            FROM likesComment
            JOIN comments ON comments.uuid = likesComment.uuid_comment
            JOIN users ON users.uuid = likesComment.uuid_user
            WHERE likesComment.uuid_comment = :uuid_comment'
        );
        $statement->execute([
            'uuid_comment' => (string) $uuid_comment
        ]);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        $statementForUserComment = $this->connection->prepare(
            'SELECT users.uuid AS user_uuid_comment, 
                    users.login, 
                    users.first_name, 
                    users.last_name,
                    users.password
            FROM users
            JOIN comments ON users.uuid = comments.author__uuid
            WHERE comments.uuid = :uuid'
        );
        $statementForUserComment->execute([
            'uuid' => (string) $uuid_comment
        ]);
        $resultForUserComment = $statementForUserComment->fetch(PDO::FETCH_ASSOC);
        $userComment = new User(
            new UUID($resultForUserComment['user_uuid_comment']),
            new Name (
                $resultForUserComment['first_name'],
                $resultForUserComment['last_name']
            ),
            $resultForUserComment['login'],
            $resultForUserComment['password']
        );

        $uuidPost = $result['post__uuid'];
        $statementForPost = $this->connection->prepare(
            'SELECT users.uuid AS user_uuid_post, 
                    users.login, 
                    users.first_name, 
                    users.last_name,
                    users.password,
                    posts.text AS post_text,
                    posts.title
            FROM users
            JOIN posts ON users.uuid = posts.author__uuid
            WHERE posts.uuid = :uuid_post'
        );
        $statementForPost->execute([
            'uuid_post' => $uuidPost
        ]);
        $resultForPost = $statementForPost->fetch(PDO::FETCH_ASSOC);
        $userPost = new User(
            $resultForPost['user_uuid_post'],
            new Name(
                $resultForPost['first_name'],
                $resultForPost['last_name']
            ),
            $resultForPost['login'],
            $resultForPost['password']
        );
        $post = new Post(
            new UUID($uuidPost),
            $userPost,
            $resultForPost['title'],
            $resultForPost['post_text']
        );
        $comment = new Comment(
            $uuid_comment,
            $userComment,
            $post,
            $result['comment_text']
        );

        if ($result === false
            || $resultForPost === false
            || $resultForUserComment === false) {
            $this->logger->warning(
                "Cannot get likes for the comment: $uuid_comment"
            );
            throw new LikeCommentNotFoundException(
                "Cannot get likes for the comment: $uuid_comment"
            );
        }

        $likesComment = [];
        foreach ($result as $value) {
            $likeComment = new LikeComment(
                $value['likes_uuid'],
                $comment,
                new User(
                    new UUID($value['uuid_user']),
                    new Name(
                        $value['first_name'],
                        $value['last_name']
                    ),
                    $value['login'],
                    $value['password']
                )
            );
            $likesComment[] = $likeComment;
        }
        return $likesComment;
    }

    /**
     * @throws LikeCommentIsAlreadyBeenCreate
     */
    public function checkLikeCommit(string $uuid_user, string $uuid_comment):bool
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likesComment 
                WHERE uuid_user = :uuid_user AND uuid_comment = :uuid_post'
        );
        $statement->execute([
            'uuid_user' => $uuid_user,
            'uuid_post' => $uuid_comment
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result !== false) {
            throw new LikeCommentIsAlreadyBeenCreate(
                "The like comment has already been created!"
            );
        }

        return true;
    }
}
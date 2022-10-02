<?php

namespace GeekBrains\LevelTwo\Http\Actions\Likes;

use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\LikeComment;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\LikesRepository\LikesCommentIRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessResponse;
use PDOException;
use Psr\Log\LoggerInterface;

class CreateLikeComment implements ActionInterface
{
    public function __construct(
        private CommentsRepositoryInterface $commentsRepository,
        private TokenAuthenticationInterface $authentication,
        private LikesCommentIRepositoryInterface $likesCommentRepository,
        private LoggerInterface $logger
    ){
    }

    public function handle(Request $request): Response
    {
        try {
            $user = $this->authentication->user($request);
        } catch (PDOException $e) {
            $this->logger->warning(
                "Cannot get user"
            );
            return new ErrorResponse($e->getMessage());
        }

        try {
            $commentUuid = new UUID($request->query('comment_uuid'));
        } catch (HttpException | InvalidArgumentException $e) {
            $this->logger->warning(
                "Cannot get comment_uuid"
            );
            return new ErrorResponse($e->getMessage());
        }

        try {
            $comment = $this->commentsRepository->get($commentUuid);
        } catch (PDOException $e) {
            $this->logger->warning(
                "Cannot get comment"
            );
            return new ErrorResponse($e->getMessage());
        }

        $newLikeCommentUuid = UUID::random();

        try {
            $likeComment = new LikeComment(
                $newLikeCommentUuid,
                $comment,
                $user
            );
        } catch (PDOException $e) {
            $this->logger->warning(
                "Cannot create a like comment"
            );
            return new ErrorResponse($e->getMessage());
        }

        try {
            $this->likesCommentRepository->checkLikeCommit($user->getUuid(), $commentUuid);
        } catch (PDOException $e) {
            $this->logger->warning(
                "The like commit has already been set"
            );
            return new ErrorResponse($e->getMessage());
        }

        $this->likesCommentRepository->save($likeComment);
        $this->logger->info("Comment saved: $newLikeCommentUuid");
        return  new SuccessResponse([
            'uuid' => (string) $newLikeCommentUuid
        ]);
    }
}
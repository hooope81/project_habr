<?php

namespace GeekBrains\LevelTwo\Http\Actions\Posts;

use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessResponse;
use PDOException;
use Psr\Log\LoggerInterface;

class DeletePost implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
        private TokenAuthenticationInterface $authentication,
        private LoggerInterface $logger

    ) {
    }
    public function handle(Request $request): Response
    {
        try {
            $this->authentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }
        try {
            $uuid = new UUID($request->query('uuid'));
        } catch (PDOException $e) {
            $this->logger->warning(
                "Cannot get uuid_post"
            );
            return new ErrorResponse($e->getMessage());
        }
        try {
            $this->postsRepository->delete($uuid);
        } catch (PDOException $e) {
            $this->logger->warning(
                "Cannot delete the post"
            );
            return new ErrorResponse($e->getMessage());
        }
        $this->logger->info("Post deleted: $uuid");
        return new SuccessResponse([
            'uuid' => $uuid . 'is delete'
        ]);
    }
}
<?php

namespace GeekBrains\LevelTwo\Http\Actions\Posts;

use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessResponse;
use PDOException;

class DeletePost implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,

    ) {
    }
    public function handle(Request $request): Response
    {
        try {
            $uuid = $request->query('uuid');
        } catch (PDOException $e) {
            return new ErrorResponse($e->getMessage());
        }
        try {
            $this->postsRepository->delete($uuid);
        } catch (PDOException $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new SuccessResponse([
            'uuid' => $uuid . 'is delete'
        ]);
    }
}
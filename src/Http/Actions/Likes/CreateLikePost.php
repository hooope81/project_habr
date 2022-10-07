<?php

namespace GeekBrains\LevelTwo\Http\Actions\Likes;

use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\LikeIsAlreadyBeenCreated;
use GeekBrains\LevelTwo\Blog\Like;
use GeekBrains\LevelTwo\Blog\Repositories\LikesRepository\LikesPostRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\Auth\TokenAuthenticationInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessResponse;
use PDOException;
use Psr\Log\LoggerInterface;

class CreateLikePost implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface     $postsRepository,
        private TokenAuthenticationInterface $authentication,
        private LikesPostRepositoryInterface $likesRepository,
        private LoggerInterface              $logger
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
            $postUuid = new UUID($request->query('post_uuid'));
        } catch (HttpException | InvalidArgumentException $e) {
            $this->logger->warning(
                "Cannot get post_uuid"
            );
            return new ErrorResponse($e->getMessage());
        }

        try {
            $post = $this->postsRepository->get($postUuid);
        } catch (PDOException $e) {
            $this->logger->warning(
                "Cannot get post"
            );
            return new ErrorResponse($e->getMessage());
        }

        $newLikeUuid = UUID::random();

        try {
            $like = new Like(
                $newLikeUuid,
                $post,
                $user
            );
        } catch (PDOException $e) {
            $this->logger->warning(
                "Cannot create a like"
            );
            return new ErrorResponse($e->getMessage());
        }

        try {
            $this->likesRepository->checkLike($user->getUuid(), $postUuid);
        } catch (PDOException $e){
            $this->logger->warning(
                "The like has already been set"
            );
            return new ErrorResponse($e->getMessage());
        }

        $this->likesRepository->save($like);
        $this->logger->info("Comment saved: $newLikeUuid");
        return  new SuccessResponse([
            'uuid' => (string) $newLikeUuid
        ]);
    }
}
<?php

namespace GeekBrains\LevelTwo\Http\Actions\Users;

use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Http\Actions\ActionInterface;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\Response;
use GeekBrains\LevelTwo\Http\SuccessResponse;
use PDOException;
use Psr\Log\LoggerInterface;

class FindByLogin implements ActionInterface
{
    public function __construct(
        private readonly UsersRepositoryInterface $usersRepository,
        private LoggerInterface $logger
    )  {
    }

    public function handle(Request $request): Response
    {
        try {
            $login = $request->query('login');
        } catch (HttpException $e) {
            $this->logger->warning(
                "Cannot get login"
            );
            return new ErrorResponse($e->getMessage());
        }
        try {
            $user = $this->usersRepository->getByLogin($login);
        } catch (UserNotFoundException $e) {
            $this->logger->warning(
                "Cannot get user"
            );
            return new ErrorResponse($e->getMessage());
        }
        $this->logger->info("User found: $login");
        return new SuccessResponse([
            'login' => $user->getLogin(),
            'name' => $user->getName()->getFirstName() . ' ' . $user->getName()->getLastName()
        ]);
    }
}
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

class FindByLogin implements ActionInterface
{
    public function __construct(
        private readonly UsersRepositoryInterface $usersRepository
    )  {
    }

    public function handle(Request $request): Response
    {
        try {
            $login = $request->query('login');
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }
        try {
            $user = $this->usersRepository->getByLogin($login);
        } catch (UserNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }
        return new SuccessResponse([
            'login' => $user->getLogin(),
            'name' => $user->getName()->getFirstName() . ' ' . $user->getName()->getLastName()
        ]);
    }
}
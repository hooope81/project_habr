<?php

namespace GeekBrains\LevelTwo\Blog\Command;

use GeekBrains\LevelTwo\Blog\Exceptions\ArgumentsException;
use GeekBrains\LevelTwo\Blog\Exceptions\CommandException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Name;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;

class CreateUserCommand
{
    public function __construct (
        private UsersRepositoryInterface $usersRepository
    ) {
    }

    /**
     * @throws CommandException
     * @throws ArgumentsException
     */
    public function handle (Arguments $arguments): void
    {

        $login = $arguments->get('login');

        if ($this->userExists($login)) {
            throw new CommandException(
                "User already exists: $login"
            );
        }

        $this->usersRepository->save(new User(
            UUID::random(),
            new Name ($arguments->get('first_name'), $arguments->get('last_name')),
            $login
        ));
    }



    private function userExists(string $login): bool
    {
        try {
            $this->usersRepository->getByLogin($login);
        } catch (UserNotFoundException) {
            return false;
        }
        return true;
    }
}
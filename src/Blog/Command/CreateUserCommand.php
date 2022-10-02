<?php

namespace GeekBrains\LevelTwo\Blog\Command;

use GeekBrains\LevelTwo\Blog\Exceptions\ArgumentsException;
use GeekBrains\LevelTwo\Blog\Exceptions\CommandException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Name;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use Psr\Log\LoggerInterface;

class CreateUserCommand
{
    public function __construct (
        private UsersRepositoryInterface $usersRepository,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws CommandException
     * @throws ArgumentsException
     */
    public function handle (Arguments $arguments): void
    {
        $this->logger->info("Create user command started");
        $login = $arguments->get('login');

        if ($this->userExists($login)) {
            $this->logger->warning(
                "User already exists: $login"
            );
            return;
        }

        $user = User::createFrom(
            $login,
            $arguments->get('password'),
            new Name(
                $arguments->get('first_name'),
                $arguments->get('last_name')
            )
        );

        $this->usersRepository->save($user);

        $this->logger->info("User created: " . $user->getUuid());
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
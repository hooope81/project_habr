<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\UsersRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Name;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use PDO;

class SqliteUsersRepository implements UsersRepositoryInterface
{
    public function __construct (
        private PDO $connection
    ) {

    }

    public function save(User $user):void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (first_name, last_name, uuid, login) 
                VALUES (:first_name, :last_name, :uuid, :login)'
        );
        $statement->execute([

                'first_name' => $user->getName()->getFirstName(),
                'last_name' => $user->getName()->getLastName(),
                'uuid' => $user->getUuid(),
                'login' => $user->getLogin()
        ]);
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function get(UUID $uuid): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE uuid = :uuid'
        );
        $statement->execute([
            'uuid' => (string) $uuid
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            throw new UserNotFoundException(
                "Cannot get user: $uuid"
            );
        }
        return $this->getUser($result, $uuid);
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function getByLogin(string $login): User
    {         $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE login = :login'
        );
        $statement->execute([
            'login' => $login
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            throw new UserNotFoundException(
                "Cannot get user: $login"
            );
        }
        $uuid = new UUID($result['uuid']);
        return $this->getUser($result, $uuid);
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function getUser($result, UUID $uuid): User
    {

        return new User(
            $uuid,
            new Name($result['first_name'], $result['last_name']),
            $result['login']
        );
    }

}
<?php
namespace GeekBrains\LevelTwo\Blog\Repositories\UsersRepository;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;

class InMemoryUsersRepository implements UsersRepositoryInterface
{
    private array $users = [];

    public function save(User $user): void
    {
        $this->users[] = $user;
    }

    /**
     * @throws UserNotFoundException
     */
    public function get(UUID $uuid): User
    {
        foreach ($this->users as $user) {
            if ((string) $user->getUuid() === (string) $uuid) {
                return $user;
            }
        }
        throw new UserNotFoundException("User not found: $uuid");
    }

    /**
     * @throws UserNotFoundException
     */
    public function getByLogin(string $login): User
    {
      foreach ($this->users as $user) {
          if ($user->getLogin() === $login) {
              return $user;
          }
      }
      throw new UserNotFoundException("User not found: $login");
    }
}
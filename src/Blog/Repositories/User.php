<?php
namespace GeekBrains\LevelTwo\Blog\Repositories;
use GeekBrains\LevelTwo\Person\Name;

class User
{
    private int $id;
    private Name $username;
    private string $login;

    /**
     * @param int $id
     * @param Name $username
     * @param string $login
     */
    public function __construct(int $id, Name $username, string $login)
    {
        $this->id = $id;
        $this->username = $username;
        $this->login = $login;
    }

    public function __toString()
    {
        return "Юзер $this->id с именем $this->username и логином $this->login" . PHP_EOL;
    }

    /**
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

}
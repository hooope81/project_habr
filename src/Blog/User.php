<?php
namespace GeekBrains\LevelTwo\Blog;

class User
{
    private int $idUser;
    private string $firstName;
    private string $lastName;

    /**
     * @param int $idUser
     * @param string $firstName
     * @param string $lastName
     */
    public function __construct(int $idUser, string $firstName, string $lastName)
    {
        $this->idUser = $idUser;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function __toString()
    {
        return "$this->firstName $this->lastName";
    }

    /**
     * @return int
     */
    public function getIdUser(): int
    {
        return $this->idUser;
    }

    /**
     * @param int $idUser
     */
    public function setIdUser(int $idUser): void
    {
        $this->idUser = $idUser;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }



}
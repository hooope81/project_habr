<?php
namespace GeekBrains\LevelTwo\Blog;
use GeekBrains\LevelTwo\Blog\User;

class Post
{
   private int $idPost;
   private User $user;
   private string $headerPost;
   private string $textPost;

    /**
     * @param int $idPost
     * @param User $user
     * @param string $headerPost
     * @param string $textPost
     */
    public function __construct(int $idPost, User $user, string $headerPost, string $textPost)
    {
        $this->idPost = $idPost;
        $this->user = $user;
        $this->headerPost = $headerPost;
        $this->textPost = $textPost;
    }

    public function __toString(): string
    {
        return $this->user . ' пишет: ' . $this->headerPost . PHP_EOL . $this->textPost . PHP_EOL;
    }

    /**
     * @return int
     */
    public function getIdPost(): int
    {
        return $this->idPost;
    }

    /**
     * @param int $idPost
     */
    public function setIdPost(int $idPost): void
    {
        $this->idPost = $idPost;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getHeaderPost(): string
    {
        return $this->headerPost;
    }

    /**
     * @param string $headerPost
     */
    public function setHeaderPost(string $headerPost): void
    {
        $this->headerPost = $headerPost;
    }

    /**
     * @return string
     */
    public function getTextPost(): string
    {
        return $this->textPost;
    }

    /**
     * @param string $textPost
     */
    public function setTextPost(string $textPost): void
    {
        $this->textPost = $textPost;
    }


}
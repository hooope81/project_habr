<?php
namespace GeekBrains\LevelTwo\Blog;

class Post
{

    public function __construct(
        private UUID $uuid,
        private User $user,
        private string $title,
        private string $text
    ){
    }

    public function __toString(): string
    {
        return $this->user . ' пишет: ' . $this->title . PHP_EOL . $this->text . PHP_EOL;
    }

    /**
     * @return UUID
     */
    public function getUuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * @param UUID $uuid
     */
    public function setUuid(UUID $uuid): void
    {
        $this->uuid = $uuid;
    }

    /**
     * @return \GeekBrains\LevelTwo\Blog\User
     */
    public function getUser(): \GeekBrains\LevelTwo\Blog\User
    {
        return $this->user;
    }

    /**
     * @param \GeekBrains\LevelTwo\Blog\User $user
     */
    public function setUser(\GeekBrains\LevelTwo\Blog\User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }



}
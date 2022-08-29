<?php
namespace GeekBrains\LevelTwo\Blog\Repositories;
use GeekBrains\LevelTwo\Person\Person;

class Post
{
    private int $id;
    private Person $author;
    private string $text;

    /**
     * @param int $id
     * @param Person $author
     * @param string $text
     */
    public function __construct(int $id, Person $author, string $text)
    {
        $this->id = $id;
        $this->author = $author;
        $this->text = $text;
    }

    public function __toString(): string
    {
        return $this->author . ' пишет: ' . $this->text . PHP_EOL;
    }
}
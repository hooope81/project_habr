<?php
namespace GeekBrains\LevelTwo\Blog;
use GeekBrains\LevelTwo\Blog\{User, Post};

class Comments
{
    private int $idComment;
    private User $user;
    private Post $post;
    private string $textComment;

    /**
     * @param int $idComment
     * @param User $user
     * @param Post $post
     * @param string $textComment
     */
    public function __construct(int $idComment, User $user, Post $post, string $textComment)
    {
        $this->idComment = $idComment;
        $this->user = $user;
        $this->post = $post;
        $this->textComment = $textComment;
    }

    public function __toString(): string
    {
        return "Комментарий к статье: " . $this->post->getHeaderPost() . PHP_EOL . $this->textComment . PHP_EOL;
    }

}

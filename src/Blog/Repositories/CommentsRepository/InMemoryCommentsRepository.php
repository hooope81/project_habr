<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository;

use GeekBrains\LevelTwo\Blog\{Comment, UUID};

use GeekBrains\LevelTwo\Blog\Exceptions\CommentNotFoundException;

class InMemoryCommentsRepository implements CommentsRepositoryInterface
{
    private array $comments = [];

    public function save(Comment $comment): void
    {
        $this->comments[] = $comment;
    }

    /**
     * @throws CommentNotFoundException
     */
    public function get(UUID $uuid): Comment
    {
        foreach ($this->comments as $comment) {
            if((string) $comment->getUuid() === (string) $uuid){
                return $comment;
            }
        }
        throw new CommentNotFoundException("Comment not found: $uuid");
    }
}
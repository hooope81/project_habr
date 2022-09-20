<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\PostsRepository;

use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\UUID;

class InMemoryPostsRepository implements PostsRepositoryInterface
{
    private array $posts = [];

    public function save(Post $post): void
    {
        $this->posts[] = $post;
    }

    /**
     * @throws PostNotFoundException
     */
    public function get(UUID $uuid): Post
    {
        foreach ($this->posts as $post) {
            if ((string) $post->getUuid() === (string) $uuid) {
                return $post;
            }
        }
        throw new PostNotFoundException("Post not found: $uuid");
    }

    public function delete(string $uuid): void
    {

    }
}
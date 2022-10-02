<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesRepository;

use GeekBrains\LevelTwo\Blog\Like;
use GeekBrains\LevelTwo\Blog\UUID;

interface LikesPostRepositoryInterface
{
    public function save(Like $like): void;

    public function getByPostUuid(UUID $uuid_post): array;
}
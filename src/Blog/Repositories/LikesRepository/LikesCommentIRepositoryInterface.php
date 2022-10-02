<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\LikesRepository;

use GeekBrains\LevelTwo\Blog\LikeComment;
use GeekBrains\LevelTwo\Blog\UUID;

interface LikesCommentIRepositoryInterface
{
    public function save(LikeComment $like): void;

    public function getByCommentUuid(UUID $uuid_comment): array;


}
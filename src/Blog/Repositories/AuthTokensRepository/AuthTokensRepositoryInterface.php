<?php

namespace GeekBrains\LevelTwo\Blog\Repositories\AuthTokensRepository;

use GeekBrains\LevelTwo\Blog\AuthToken;

interface AuthTokensRepositoryInterface
{
    public function save(AuthToken $authToken): void;

    public function get(string $token): AuthToken;
}
<?php

use GeekBrains\LevelTwo\Blog\{Command\Arguments,
    Command\CreateUserCommand,
    Comment,
    Exceptions\AppException,
    Post,
    Repositories\CommentsRepository\SqliteCommentsRepository,
    Repositories\LikesRepository\SqliteLikesRepository,
    Repositories\PostsRepository\InMemoryPostsRepository,
    Repositories\PostsRepository\SqlitePostsRepository,
    User,
    Name,
    Like,
    UUID};
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;

$container = require __DIR__ . '/bootstrap.php';

$command = $container->get(CreateUserCommand::class);

try {
    $command->handle(Arguments::fromArgv($argv));
} catch (AppException $e) {
    echo "{$e->getMessage()}\n";
}


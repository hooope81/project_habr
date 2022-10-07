<?php

use GeekBrains\LevelTwo\Blog\{Command\Arguments,
    Command\CreateUserCommand,
    Command\FakeData\PopulateDB,
    Command\Posts\DeletePost,
    Command\Users\CreateUser,
    Command\Users\UpdateUser,
    Comment,
    Exceptions\AppException,
    Post,
    Repositories\CommentsRepository\SqliteCommentsRepository,
    Repositories\LikesRepository\SqliteLikesPostRepository,
    Repositories\PostsRepository\InMemoryPostsRepository,
    Repositories\PostsRepository\SqlitePostsRepository,
    User,
    Name,
    Like,
    UUID};
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;

$container = require __DIR__ . '/bootstrap.php';

$application = new Application();

$commandsClasses = [
    CreateUser::class,
    DeletePost::class,
    UpdateUser::class,
    PopulateDB::class
];

foreach ($commandsClasses as $commandClass) {
    $command = $container->get($commandClass);
    $application->add($command);
}

$application->run();


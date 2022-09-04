<?php

use GeekBrains\LevelTwo\Blog\{Command\Arguments,
    Command\CreateUserCommand,
    Comment,
    Exceptions\AppException,
    Post,
    Repositories\CommentsRepository\SqliteCommentsRepository,
    Repositories\PostsRepository\SqlitePostsRepository,
    UUID};
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;

include __DIR__ . "/vendor/autoload.php";

$userRepository = new SqliteUsersRepository(
    new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
);
$command = new CreateUserCommand($userRepository);

try {
    $command->handle(Arguments::fromArgv($argv));
} catch (AppException $e) {
    echo "{$e->getMessage()}\n";
}

try {
    $user = $userRepository->get(new UUID('1c07ad19-0974-40f6-8997-e0466140e4b4'));
    $post = new Post(UUID::random(), $user, 'опять осень', 'я календарь переверну, и снова третье сентябряяя');
    $postRepository = new SqlitePostsRepository(
        new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
    );
    $post2 = $postRepository->get(new UUID('2828a5e4-fd13-4160-9ed9-16fc695a5d07'));
    $user2 = $userRepository->get(new UUID('66d19285-a096-4373-bd61-4f6ca6eb8fdd'));
    $comment = new Comment(UUID::random(),$user2, $post2, 'да-да');
    $commentRepository = new SqliteCommentsRepository(
        new PDO('sqlite:' . __DIR__ . '/blog.sqlite')
    );
    $commentRepository->save($comment);
    echo $commentRepository->get(new UUID('986dbfb0-42e4-4d07-af4b-74601cffb3eb'));
} catch (AppException $e) {
    echo "{$e->getMessage()}\n";
}


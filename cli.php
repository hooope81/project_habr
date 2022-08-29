<?php
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\{Comments, Post, User};
use GeekBrains\LevelTwo\Blog\Repositories\InMemoryUsersRepository;

include __DIR__ . "/vendor/autoload.php";
//spl_autoload_register('load');
//
//function load($className): void
//{
//    $file = $className . ".php";
//    $file = str_replace('GeekBrains\LevelTwo', 'src', $file);
//    $file = str_replace('\\', '/', $file);
//    if (file_exists($file)) {
//        include $file;
//    }
//}

$faker = Faker\Factory::create('ru_RU');
$firstName = $faker->firstName;
$lastName = $faker->lastName;

$user = new User($argv[1], $firstName, $lastName);
$post = new Post(1, $user, $faker->realText(rand(10, 15)), $faker->realText(rand(100, 200)));
$comment = new Comments(1,$user, $post, $faker->realText(rand(10, 30)));

echo $post;
echo $comment;

$userRepository = new InMemoryUsersRepository();
try {
    $userRepository->save($user);
    echo $userRepository->get(1);
    echo $userRepository->get(2);
} catch (UserNotFoundException $e) {
    echo $e->getMessage();
}
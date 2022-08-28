<?php
use GeekBrains\LevelTwo\Person\{Name, Person};
use GeekBrains\LevelTwo\Blog\Repositories\{Post, User, InMemoryUsersRepository};
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;

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

$name = new Name ('Mary', 'Smith');
$person = new Person ($name, new DateTimeImmutable());
$user = new User (1, $name, 'mary111');
$post = new Post(1, $person, 'hi!');

$faker = Faker\Factory::create('ru_RU');
echo $faker->name() . PHP_EOL;
echo $faker->realText(rand(100, 200)) . PHP_EOL;

echo $user;
echo $post;

$userRepository = new InMemoryUsersRepository();
try {
    $userRepository->save($user);
    echo $userRepository->get(1);
    echo $userRepository->get(2);
} catch (UserNotFoundException $e) {
    echo $e->getMessage();
}
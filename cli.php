<?php
use GeekBrains\LevelTwo\Person\{Name, Person};
use GeekBrains\LevelTwo\Blog\Repositories\{Post, User};

spl_autoload_register('load');

function load($className): void
{
    $file = $className . ".php";
    $file = str_replace('GeekBrains\LevelTwo', 'src', $file);
    $file = str_replace('\\', '/', $file);
    if (file_exists($file)) {
        include $file;
    }
}

$name = new Name ('Mary', 'Smith');
$person = new Person ($name, new DateTimeImmutable());
$user = new User (1, $name, 'mary111');
$post = new Post(1, $person, 'hi!');

echo $user;
echo $post;
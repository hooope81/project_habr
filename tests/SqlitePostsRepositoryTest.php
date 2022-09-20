<?php

namespace GeekBrains\LevelTwo;

use GeekBrains\LevelTwo\Blog\Name;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\{Exceptions\InvalidArgumentException,
    Exceptions\PostNotFoundException,
    Repositories\PostsRepository\SqlitePostsRepository,
    User,
    Post};
use GeekBrains\LevelTwo\Blog\UUID;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqlitePostsRepositoryTest extends TestCase
{
    public function testItThrowsAnExceptionWhenPostNotFound(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createMock(PDOStatement::class);

        $statementStub->method('fetch')->willReturn(false);
        $connectionStub->method('prepare')->willReturn($statementStub);

        $repository = new SqlitePostsRepository($connectionStub);
        $this->expectException(PostNotFoundException::class);
        $this->expectExceptionMessage("Cannot get post: 2828a5e4-fd13-4160-9ed9-16fc695a5d07");
        $repository->get(new UUID('2828a5e4-fd13-4160-9ed9-16fc695a5d07'));

    }

    public function testItSavesPostToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                'uuid' => '2828a5e4-fd13-4160-9ed9-16fc695a5d07',
                'author__uuid' => '1c07ad19-0974-40f6-8997-e0466140e4b4',
                'title' => 'опять осень',
                'text' => 'я календарь переверну, и снова третье сентябряяя'
            ]);
        $connectionStub->method('prepare')->willReturn($statementMock);
        $repository = new SqlitePostsRepository($connectionStub);
        $user = new User(
            new UUID('1c07ad19-0974-40f6-8997-e0466140e4b4'),
            new Name ('Анна', 'Петрова'),
            'anna05'
        );
        $repository->save(
            new Post(
                new UUID('2828a5e4-fd13-4160-9ed9-16fc695a5d07'),
                $user,
                'опять осень',
                'я календарь переверну, и снова третье сентябряяя',
            )
        );
    }

    /**
     * @throws PostNotFoundException
     * @throws InvalidArgumentException
     */
    public function testItGetsPostByUuid(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock->method('fetch')->willReturn([
            'uuid' => '2828a5e4-fd13-4160-9ed9-16fc695a5d07',
            'title' => 'опять осень',
            'text' => 'я календарь переверну, и снова третье сентябряяя',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
            'login' => 'Ivan07',
            'user_uuid' => '1c07ad19-0974-40f6-8997-e0466140e4b4'
        ]);
        $connectionStub->method('prepare')->willReturn($statementMock);

        $postRepository = new SqlitePostsRepository($connectionStub);
        $post = $postRepository->get(new UUID('2828a5e4-fd13-4160-9ed9-16fc695a5d07'));
        $this->assertSame('2828a5e4-fd13-4160-9ed9-16fc695a5d07', (string) $post->getUuid());

    }
}
<?php

namespace GeekBrains\LevelTwo;

use GeekBrains\Blog\UnitTests\DummyLogger;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use GeekBrains\LevelTwo\Blog\{Exceptions\InvalidArgumentException, UUID, User, Name};
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqliteUsersRepositoryTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testItThrowsAnExceptionWhenUserNotFound(): void
    {

        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);


        $statementStub->method('fetch')->willReturn(false);
        $connectionStub->method('prepare')->willReturn($statementStub);

        $repository = new SqliteUsersRepository($connectionStub, new DummyLogger());

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("Cannot get user: Ivan07");

        $repository->getByLogin('Ivan07');
    }

    public function testItSavesUserToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                'first_name' => 'Ivan',
                'last_name' => 'Nikitin',
                'uuid' => '66d19285-a096-4373-bd61-4f6ca6eb8fdd',
                'login' => 'Ivan07'
            ]);

        $connectionStub->method('prepare')->willReturn($statementMock);
        $repository = new SqliteUsersRepository($connectionStub, new DummyLogger());
        $repository->save(
            new User(
                new UUID('66d19285-a096-4373-bd61-4f6ca6eb8fdd'),
                new Name ('Ivan', 'Nikitin'),
                'Ivan07'
            )
        );
    }
}
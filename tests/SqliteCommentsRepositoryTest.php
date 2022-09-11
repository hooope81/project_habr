<?php

namespace GeekBrains\LevelTwo;

use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Exceptions\CommentNotFoundException;
use GeekBrains\LevelTwo\Blog\Name;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;


class SqliteCommentsRepositoryTest extends TestCase
{
    public function testItThrowsAnExceptionWhenCommentNotFound(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createMock(PDOStatement::class);
        $statementStub->method('fetch')->willReturn(false);
        $connectionStub->method('prepare')->willReturn($statementStub);

        $repository = new SqliteCommentsRepository($connectionStub);
        $this->expectException(CommentNotFoundException::class);
        $this->expectExceptionMessage("Cannot get comment: 986dbfb0-42e4-4d07-af4b-74601cffb3eb");
        $repository->get(new UUID('986dbfb0-42e4-4d07-af4b-74601cffb3eb'));
    }

    public function testItSavesCommentToDatabase(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                'uuid' => '986dbfb0-42e4-4d07-af4b-74601cffb3eb',
                'author__uuid' => '1c07ad19-0974-40f6-8997-e0466140e4b4',
                'post__uuid' => '2828a5e4-fd13-4160-9ed9-16fc695a5d07',
                'text' => 'да-да'
            ]);
        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqliteCommentsRepository($connectionStub);
        $user = new User(
            new UUID('1c07ad19-0974-40f6-8997-e0466140e4b4'),
            new Name ('Анна', 'Петрова'),
            'anna05'
        );
        $post = new Post(
            new UUID('2828a5e4-fd13-4160-9ed9-16fc695a5d07'),
            $user,
            'опять осень',
            'я календарь переверну, и снова третье сентябряяя',
        );
        $repository->save(
            new Comment(
                new UUID('986dbfb0-42e4-4d07-af4b-74601cffb3eb'),
                $user,
                $post,
                'да-да'
            )
        );
    }

    public function testItGetsCommentByUuid(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock->method('fetch')->willReturn([
            'uuid' => '986dbfb0-42e4-4d07-af4b-74601cffb3eb',
            'author__uuid' => '1c07ad19-0974-40f6-8997-e0466140e4b4',
            'post__uuid' => '2828a5e4-fd13-4160-9ed9-16fc695a5d07',
            'text' => 'да-да',
            'title' => 'опять осень',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
            'login' => 'Ivan07',
        ]);
        $connectionStub->method('prepare')->willReturn($statementMock);
        $commentRepository = new SqliteCommentsRepository($connectionStub);
        $comment = $commentRepository->get(new UUID('986dbfb0-42e4-4d07-af4b-74601cffb3eb'));
        $this->assertSame('986dbfb0-42e4-4d07-af4b-74601cffb3eb', (string) $comment->getUuid());
    }
}
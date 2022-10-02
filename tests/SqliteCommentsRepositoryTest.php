<?php

namespace GeekBrains\LevelTwo;

use GeekBrains\Blog\UnitTests\DummyLogger;
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

        $repository = new SqliteCommentsRepository($connectionStub, new DummyLogger());
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

        $repository = new SqliteCommentsRepository($connectionStub, new DummyLogger());
        $user = new User(
            new UUID('1c07ad19-0974-40f6-8997-e0466140e4b4'),
            new Name ('Анна', 'Петрова'),
            'anna05',
            'ea23f38f7597722815ff13a88cbdfff3d988f8f9c3698ab7af37ab8fbe2dfccd'
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
            'comment_uuid' => '986dbfb0-42e4-4d07-af4b-74601cffb3eb',
            'user_uuid' => '1c07ad19-0974-40f6-8997-e0466140e4b4',
            'post_uuid' => '2828a5e4-fd13-4160-9ed9-16fc695a5d07',
            'comment_text' => 'да-да',
            'title' => 'опять осень',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
            'login' => 'Ivan07',
            'post_text' => 'some text',
            'user_uuid_comment' => '1c07ad19-0974-40f6-8997-e0466140e4b4',
            'user_first_name_comment' => 'Anna',
            'user_last_name_comment' => 'Petrova',
            'user_login_comment' => 'An8',
            'password' => 'ea23f38f7597722815ff13a88cbdfff3d988f8f9c3698ab7af37ab8fbe2dfccd',
            'user_password_comment' => '7a23f38f7597722815ff13a88cbdfff3d988f8f9c3698ab7af37ab8fbe2dfccd'
        ]);
        $connectionStub->method('prepare')->willReturn($statementMock);
        $commentRepository = new SqliteCommentsRepository($connectionStub, new DummyLogger());
        $comment = $commentRepository->get(new UUID('986dbfb0-42e4-4d07-af4b-74601cffb3eb'));
        $this->assertSame('986dbfb0-42e4-4d07-af4b-74601cffb3eb', (string) $comment->getUuid());
    }
}
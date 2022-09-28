<?php

namespace Actions;

use GeekBrains\Blog\UnitTests\DummyIdentification;
use GeekBrains\Blog\UnitTests\DummyIdentificationIsNull;
use GeekBrains\Blog\UnitTests\DummyLogger;
use GeekBrains\LevelTwo\Blog\Exceptions\AppException;
use GeekBrains\LevelTwo\Blog\Exceptions\AuthException;
use GeekBrains\LevelTwo\Blog\Exceptions\HttpException;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;
use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Name;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Actions\Posts\CreatePost;
use GeekBrains\LevelTwo\Http\Auth\IdentificationInterface;
use GeekBrains\LevelTwo\Http\Auth\JsonBodyUsernameIdentification;
use GeekBrains\LevelTwo\Http\Auth\JsonBodyUuidIdentification;
use GeekBrains\LevelTwo\Http\ErrorResponse;
use GeekBrains\LevelTwo\Http\Request;
use GeekBrains\LevelTwo\Http\SuccessResponse;
use PHPUnit\Framework\TestCase;

class CreatePostTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsSuccessResponse(): void
    {
        $request = new Request([], [],
            '{"author_uuid":"123e4567-e89b-12d3-a456-426614174001","title":"September","text":"xo-xo-xo"}');

        $postRepository = $this->createStub(PostsRepositoryInterface::class);
        $identification = $this->createStub(JsonBodyUsernameIdentification::class);
        $identification
            ->method('user')
            ->willReturn(
                new User(
                    new UUID("123e4567-e89b-12d3-a456-426614174001"),
                    new Name('Ivan', 'Nikitin'),
                    'Ivan07'
                )
            );
        $action = new CreatePost($postRepository, $identification, new DummyLogger());
        $response = $action->handle($request);
        $this->assertInstanceOf(SuccessResponse::class, $response);
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfUserNotFound(): void
    {
        $request = new Request([], [], '{"author_uuid":"10373537-0805-4d7a-830e-22b481b4859c","title":"title","text":"text"}');
        $postRepository = $this->createStub(PostsRepositoryInterface::class);
        $identification = $this->createStub(JsonBodyUsernameIdentification::class);
        $identification
            ->method('user')
            ->willThrowException(
                new AuthException('Cannot get user: 10373537-0805-4d7a-830e-22b481b4859c')
            );

        $action = new CreatePost($postRepository, $identification, new DummyLogger());

        $response = $action->handle($request);

        $response->send();

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Cannot get user: 10373537-0805-4d7a-830e-22b481b4859c"}');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfUuidIsNotRightFormat(): void
    {
        $request = new Request([], [],
            '{"author_uuid":"123","title":"September","text":"xo-xo-xo"}');
        $postRepository = $this->createStub(PostsRepositoryInterface::class);
        $identification = $this->createStub(JsonBodyUsernameIdentification::class);
        $identification
            ->method('user')
            ->willThrowException(
                new AuthException('Malformed UUID: 123')
            );

        $action = new CreatePost($postRepository, $identification, new DummyLogger());
        $response = $action->handle($request);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Malformed UUID: 123"}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfNotAllData(): void
    {
        $request = new Request([], [],
            '{"author_uuid":"123e4567-e89b-12d3-a456-426614174001","title":"September"}');

        $postRepository = $this->createStub(PostsRepositoryInterface::class);
        $identification = $this->createStub(JsonBodyUsernameIdentification::class);
        $identification
            ->method('user')
            ->willReturn(
                new User(
                    new UUID("123e4567-e89b-12d3-a456-426614174001"),
                    new Name('Ivan', 'Nikitin'),
                    'Ivan07'
                )
            );
        $action = new CreatePost(
            $postRepository,
            $identification,
            new DummyLogger()
        );
        $response = $action->handle($request);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such field: text"}');
        $response->send();
    }

}
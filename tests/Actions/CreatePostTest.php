<?php

namespace Actions;

use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Exceptions\UserNotFoundException;
use GeekBrains\LevelTwo\Blog\Name;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Http\Actions\Posts\CreatePost;
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

        $usersRepository = $this->usersRepository([
            new User(
                new UUID("123e4567-e89b-12d3-a456-426614174001"),
                new Name('Ivan', 'Nikitin'),
                'Ivan07'
            )
        ]);

        $postRepository = $this->postsRepository();
        $action = new CreatePost($postRepository, $usersRepository);
        $response = $action->handle($request);
        $this->assertInstanceOf(SuccessResponse::class, $response);
        $this->setOutputCallback(function ($data){
            $dataDecode = json_decode(
                $data,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
            $dataDecode['data']['uuid'] = "123e4567-e89b-12d3-a456-426614174000";
            return json_encode(
                $dataDecode,
                JSON_THROW_ON_ERROR
            );
        });

        $this->expectOutputString('{"success":true,"data":{"uuid":"123e4567-e89b-12d3-a456-426614174000"}}');
        $response->send();
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfUserNotFound(): void
    {
        $request = new Request([], [],
            '{"author_uuid":"123e4567-e89b-12d3-a456-426614174001","title":"September","text":"xo-xo-xo"}');
        $usersRepository = $this->usersRepository([]);
        $postsRepository = $this->postsRepository([]);
        $action = new CreatePost($postsRepository, $usersRepository);
        $response = $action->handle($request);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Cannot find user: 123e4567-e89b-12d3-a456-426614174001"}');
        $response->send();
    }
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfUuidIsNotRightFormat(): void
    {
        $request = new Request([], [],
            '{"author_uuid":"123","title":"September","text":"xo-xo-xo"}');
        $usersRepository = $this->usersRepository([]);
        $postsRepository = $this->postsRepository([]);
        $action = new CreatePost($postsRepository, $usersRepository);
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
        $usersRepository = $this->usersRepository([
            new User(
                new UUID("123e4567-e89b-12d3-a456-426614174001"),
                new Name('Ivan', 'Nikitin'),
                'Ivan07'
            )
        ]);
        $postsRepository = $this->postsRepository([]);
        $action = new CreatePost($postsRepository, $usersRepository);
        $response = $action->handle($request);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such field: text"}');
        $response->send();
    }

    private function postsRepository(): PostsRepositoryInterface
    {
        return new class() implements PostsRepositoryInterface {

            public function save(Post $post): void
            {

            }
            public function get(UUID $uuid): Post
            {
                throw new PostNotFoundException('Not found');
            }

            public function delete(string $uuid): void
            {
                // TODO: Implement delete() method.
            }
        };
    }
    private function usersRepository(array $users): UsersRepositoryInterface
    {
        return new class($users) implements UsersRepositoryInterface
        {
            public function __construct(
                private array $users
            )
            {
            }
            public function save(User $user): void
            {
            }

            public function get(UUID $uuid): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && (string)$uuid == $user->getUuid()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException('Cannot find user: ' . $uuid);
            }
            public function getByLogin(string $login): User
            {
                throw new UserNotFoundException('Not found');
            }
        };
    }
}
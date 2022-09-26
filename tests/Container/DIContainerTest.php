<?php

namespace GeekBrains\Blog\UnitTests\Container;

use GeekBrains\LevelTwo\Blog\Container\DIContainer;
use GeekBrains\LevelTwo\Blog\Exceptions\NotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\InMemoryUsersRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use PHPUnit\Framework\TestCase;

class DIContainerTest extends TestCase
{
    public function testItThrowsAnExceptionIfCannotResolveType(): void
    {
        $container = new DIContainer();

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(
            "Cannot resolve type: GeekBrains\Blog\UnitTests\Container\SomeClass"
        );

        $container->get(SomeClass::class);
    }

    public function testItResolvesClassWithoutDependencies(): void
    {
        $container = new DIContainer();
        $object = $container->get(SomeClassWithoutDependencies::class);

        $this->assertInstanceOf(
            SomeClassWithoutDependencies::class,
            $object
        );
    }

    public function testItResolvesClassByContract(): void
    {
        $container = new DIContainer();
        $container->bind(
            UsersRepositoryInterface::class,
            InMemoryUsersRepository::class
        );

        $object = $container->get(UsersRepositoryInterface::class);

        $this->assertInstanceOf(
            InMemoryUsersRepository::class,
            $object
        );
    }

    public function testItReturnsPredefinedObject():void
    {
        $container = new DIContainer();
        $container->bind(
            SomeClassWithParameter::class,
            new SomeClassWithParameter(42)
        );

        $object = $container->get(SomeClassWithParameter::class);

        $this->assertInstanceOf(
            SomeClassWithParameter::class,
            $object
        );

        $this->assertSame(42, $object->value());
    }

    public function testItResolvesClassWithDependencies(): void
    {
        $container = new DIContainer();
        $container->bind(
            SomeClassWithParameter::class,
            new SomeClassWithParameter(42)
        );
        $object = $container->get(ClassDependingOnAnother::class);

        $this->assertInstanceOf(
            ClassDependingOnAnother::class,
            $object
        );

    }
}
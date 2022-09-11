<?php

namespace GeekBrains\LevelTwo\Command;

use GeekBrains\LevelTwo\Blog\Exceptions\ArgumentsException;
use PHPUnit\Framework\TestCase;
use GeekBrains\LevelTwo\Blog\Command\Arguments;
class ArgumentsTest extends TestCase
{
    public function testItReturnsArgumentsValueByName(): void
    {
        $arguments = new Arguments(['some_key' => 'some_value']);
        $value = $arguments->get('some_key');
        $this->assertEquals('some_value', $value);
    }

    /**
     * @dataProvider argumentsProvider
     */
    public function testItConvertsArgumentsToStrings(
        $inputValue,
        $expectedValue
    ): void
    {
        $arguments = new Arguments(['some_key' => $inputValue]);
        $value = $arguments->get('some_key');
        $this->assertSame($expectedValue, $value);
    }

    public function testItThrowsAnExceptionWhenArgumentIsAbsent(): void
    {
        $arguments = new Arguments([]);
        $this->expectException(ArgumentsException::class);
        $this->expectDeprecationMessage("No such argument: some_key");
        $arguments->get('some_key');
    }

    public function argumentsProvider(): iterable
    {
        return [
            ['some_string', 'some_string'],
            [' some_string', 'some_string'],
            [' some_string ', 'some_string'],
            [123, '123'],
            [12.3, '12.3'],
        ];
    }
}

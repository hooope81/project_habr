<?php
namespace GeekBrains\LevelTwo\Person;
use \DateTimeImmutable;

class Person
{
    private Name $name;
    private DateTimeImmutable $registeredOn;

    /**
     * @param Name $name
     * @param DateTimeImmutable $registeredOn
     */
    public function __construct(Name $name, DateTimeImmutable $registeredOn)
    {
        $this->name = $name;
        $this->registeredOn = $registeredOn;
    }

    public function __toString(): string
    {
        return $this->name . ' (на сайте с ' . $this->registeredOn->format('Y-m-d') . ')';
    }
}
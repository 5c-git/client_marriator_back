<?php

namespace App\Enum\Role;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;
use App\Models\Fields\Directory\Project;
use App\Models\Fields\Directory\Place;

enum RoleEnum: int
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case admin = 1;
    case client = 2;
    case manager = 3;
    case recruiter = 4;
    case specialist = 5;
    case supervisor = 6;

    public function getUserBinding(): string
    {
        return match($this)
        {
            self::client => Project::class,
            self::manager => Project::class,
            self::recruiter => Place::class,
            self::supervisor => Project::class,
        };
    }

    public function getUserBindingFunction(): string
    {
        return match($this)
        {
            self::client => 'project',
            self::manager => 'project',
            self::recruiter => 'place',
            self::supervisor => 'project',
        };
    }

    public function getUserBindingName(): string
    {
        return match($this)
        {
            self::client => 'Проект',
            self::manager => 'Проект',
            self::recruiter => 'Место проведения',
            self::supervisor => 'Проект',
        };
    }

    public function getClientForModeration(): array
    {
        return match($this)
        {
            self::admin => [
                self::client->value,
                self::manager->value,
                self::recruiter->value,
                self::specialist->value,
                self::supervisor->value
            ],
            self::client => [self::client],
            self::manager => [self::manager],
            self::recruiter => [self::recruiter],
            self::supervisor => [self::supervisor],
        };
    }
}

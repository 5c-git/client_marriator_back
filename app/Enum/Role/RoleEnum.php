<?php

namespace App\Enum\Role;
use App\Models\Fields\Directory\Counterparty;
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

    public function getUserBinding(): array
    {
        return match($this)
        {
            self::client => [Counterparty::class,Project::class,Place::class],
            self::manager => [Project::class],
            self::recruiter => [],
            self::supervisor => [],
            self::specialist => [],
            self::admin => [],
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
            self::specialist => 'project',
            self::admin => 'project',
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
            self::specialist => 'project',
            self::admin => 'project',
        };
    }

    public function getClientForModeration(): array
    {
        return match($this)
        {
            self::admin => [
                self::client->value,
                self::manager->value,
                self::specialist->value,
                self::supervisor->value
            ],
            self::client => [],
            self::manager => [
                self::client->value,
                self::specialist->value,
                self::supervisor->value
            ],
            self::recruiter => [],
            self::supervisor => [
                self::client->value,
                self::specialist->value
            ],
            self::specialist => [],
        };
    }
}

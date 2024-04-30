<?php

namespace App\Enum\Fields;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;

use App\Models\Fields\Directory\Country;
use App\Models\Fields\Directory\Bank;

enum FieldsDirectoryEnum: string
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case country = Country::class;
    case bank = Bank::class;

    public function directoryName(): string
    {
        return match($this)
        {
            self::country => 'Справочник стран',
            self::bank => 'Справочник банков',
        };
    }

}

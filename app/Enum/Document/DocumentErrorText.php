<?php

namespace App\Enum\Document;
use App\Models\Fields\Directory\Place;
use App\Models\Fields\Directory\Project;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;
use App\Services\Formatter\Connectors;

enum DocumentErrorText: int
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case ErrorUpload = 1;
    case ErrorPhp = 2;
    case ErrorRecognize = 3;
    case ErrorFileType = 4;

    public function getUserBinding(): string
    {
        return match($this)
        {
            self::ErrorUpload => 'Ошибка загрузки файла загрузите новый файл',
            self::ErrorPhp => 'Ошибка обратитесь в тех поддержку',
            self::ErrorRecognize => 'Ошибка распознавания документа загрузите новое изображение или обратитесь в тех поддержку',
            self::ErrorFileType => 'Приложенный файл не соответствует полю , приложите необходимый файл',
        };
    }
}

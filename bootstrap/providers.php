<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class,
    App\Providers\ValidationServiceProvider::class,
    Modules\Questionnaire\Providers\QuestionnaireServiceProvider::class,
    Modules\YandexSmena\Providers\YandexSmenaServiceProvider::class,
];

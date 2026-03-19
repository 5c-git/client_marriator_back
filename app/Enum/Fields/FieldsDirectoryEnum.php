<?php

namespace App\Enum\Fields;
use App\Models\Fields\Directory\Age;
use App\Models\Fields\Directory\Documentation;
use App\Models\Fields\Directory\MedicalBook;
use App\Models\Fields\Directory\Organization;
use App\Models\Fields\Directory\Phone;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Names;
use ArchTech\Enums\Values;
use ArchTech\Enums\Options;

use App\Models\Fields\Directory\Country;
use App\Models\Fields\Directory\Bank;
use App\Models\Fields\Directory\Activities;
use App\Models\Fields\Directory\Citizenship;
use App\Models\Fields\Directory\ClothingSize;
use App\Models\Fields\Directory\Gender;
use App\Models\Fields\Directory\HairColor;
use App\Models\Fields\Directory\HairLength;
use App\Models\Fields\Directory\Height;
use App\Models\Fields\Directory\Messengers;
use App\Models\Fields\Directory\OfferSearch;
use App\Models\Fields\Directory\RegionOfResidence;
use App\Models\Fields\Directory\Residence;
use App\Models\Fields\Directory\ShoeSize;
use App\Models\Fields\Directory\TaxStatus;
use App\Models\Fields\Directory\ViewActivities;
use App\Models\Fields\Directory\Weight;


enum FieldsDirectoryEnum: string
{
    use Options;
    use InvokableCases;
    use Names;
    use Values;

    case country = Country::class;
    case bank = Bank::class;
    case activities = Activities::class;
    case citizenship = Citizenship::class;
    case clothingSize = ClothingSize::class;
    case gender = Gender::class;
    case hairColor = HairColor::class;
    case hairLength = HairLength::class;
    case height = Height::class;
    case messengers = Messengers::class;
    case offerSearch = OfferSearch::class;
    case regionOfResidence = RegionOfResidence::class;
    case residence = Residence::class;
    case shoeSize = ShoeSize::class;
    case taxStatus = TaxStatus::class;
    case viewActivities = ViewActivities::class;
    case weight = Weight::class;
    case documentation = Documentation::class;
    case organization = Organization::class;
    case age = Age::class;
    case medicalBook = MedicalBook::class;
    case phone = Phone::class;

    public function directoryName(): string
    {
        return match($this)
        {
            self::country => 'Справочник стран',
            self::bank => 'Справочник банков',
            self::activities => 'Справочник направления деятельности',
            self::citizenship => 'Справочник гражданств',
            self::clothingSize => 'Справочник размер одежды',
            self::gender => 'Справочник пол',
            self::hairColor => 'Справочник цвет волос',
            self::hairLength => 'Справочник длина волос',
            self::height => 'Справочник рост, см.',
            self::messengers => 'Справочник мессенджеры',
            self::offerSearch => 'Справочник территории поиска предложений',
            self::regionOfResidence => 'Справочник регион проживания',
            self::residence => 'Разрешение на проживание на территории РФ',
            self::shoeSize => 'Справочник размеров обуви',
            self::taxStatus => 'Справочник налоговый статус',
            self::viewActivities => 'Справочник виды деятельности',
            self::weight => 'Справочник вес, кг.',
            self::documentation => 'Справочник документов',
            self::organization => 'Справочник организаций',
            self::age => 'Справочник возраст',
            self::medicalBook => 'Справочник мед книжка',
            self::phone => 'Справочник моделей телефона',
        };
    }

}

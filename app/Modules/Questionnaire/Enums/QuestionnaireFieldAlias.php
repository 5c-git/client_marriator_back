<?php

namespace Modules\Questionnaire\Enums;

/**
 * Maps questionnaire field UUIDs (as stored in users.data) to semantic keys
 * used by validation/enrichment steps.
 */
enum QuestionnaireFieldAlias: string
{
    case FIRST_NAME = 'first_name';
    case LAST_NAME = 'last_name';
    case PATRONYMIC = 'patronymic';
    case PHONE = 'phone';
    case INN = 'inn';
    case SNILS = 'snils';
    case CARD_NUMBER = 'card_number';
    case ACCOUNT_NUMBER = 'account_number';
    case BANK_BIK = 'bank_bik';
    case GENDER = 'gender';
    case TAX_STATUS = 'tax_status';
    case CITIZENSHIP = 'citizenship';
    case ACTIVITY_TYPE = 'activity_type';
    case ACTIVITY_DIRECTION = 'activity_direction';
    case DOCUMENT_TYPE = 'document_type';
    case ORGANIZATIONS = 'organizations';
    case MESSENGER = 'messenger';
    case CARD_EXPIRY = 'card_expiry';

    /**
     * UUID of the field in the registration form / users.data.
     */
    public function uuid(): string
    {
        return match ($this) {
            self::FIRST_NAME => 'QsZI3i3WLzO5rNO2ZJjXBtx9nJBosd',
            self::LAST_NAME => 'X2CSwnQZntQEdPc1Xq5lgeLahytrna',
            self::PATRONYMIC => 'NU6zM7fYI1dK3MV9MITtlpvhybVkez',
            self::PHONE => 'MlWbSSwUynanXXhjbIm3LwN5MWCoW3',
            self::INN => '9nuDjP3c3Ule99uIiPArhyE1rssGHF',
            self::SNILS => 'IloSAoeA5hNj6iKQuM3saaBSmw7nvC',
            self::CARD_NUMBER => 'HpecJclZpUWpkI7voOZmYrjv06PG3a',
            self::ACCOUNT_NUMBER => 'n1bZyr8bWZYmCOcT5KFQg1MRqvQzj2',
            self::BANK_BIK => '5nibSuUwMDHHB965TRu8iT4exCxhRN',
            self::GENDER => 'unC20BLqzsZbEEGWlnT663EkueUBUi',
            self::TAX_STATUS => 'nalogstatus',
            self::CITIZENSHIP => 'gov',
            self::ACTIVITY_TYPE => 'vidideayt',
            self::ACTIVITY_DIRECTION => 'testitem',
            self::DOCUMENT_TYPE => 'R7ydKRH6YRdU85KI2UIql0EDWNyvnr',
            self::ORGANIZATIONS => 'RjJLN6y6qDD9KBwKyKFbnaIlLRRiMs',
            self::MESSENGER => 'pnIXSuSWPMsx1T5IVWU7x9SNBZ7Ss4',
            self::CARD_EXPIRY => 'ktnWQlExQvDV7ucFdFOyW7ekL8aREv',
        };
    }

    /**
     * Build a lookup map uuid => semantic key.
     *
     * @return array<string, string>
     */
    public static function uuidToKeyMap(): array
    {
        $map = [];
        foreach (self::cases() as $alias) {
            $map[$alias->uuid()] = $alias->value;
        }

        return $map;
    }
}

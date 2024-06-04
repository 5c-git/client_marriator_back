<?php
namespace App\Services\Formatter;

interface FormaterInterface
{
    public static function createFormat($fieldsData,$value):array;
}

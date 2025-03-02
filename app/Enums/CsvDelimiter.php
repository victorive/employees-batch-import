<?php

namespace App\Enums;

enum CsvDelimiter: string
{
    case COMMA = ',';
    case SEMICOLON = ';';
    case TAB = '\t';
    case PIPE = '|';

    public static function getAllAsArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}

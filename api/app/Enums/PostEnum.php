<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum PostEnum: string
{
    use EnumTrait;

    // ITEMS_PER_PAGE
    case MinItemsPerPage = '1';
    case MaxItemsPerPage = '20';

    // TAGS
    case Php = 'php';
    case Ruby = 'ruby';
    case Java = 'java';
    case Javascript = 'javascript';
    case Bash = 'bash';

    // SORTABLE_FIELDS
    case Title = 'title';
    case Content = 'content';

    // SORT_ORDER_OPTIONS
    case Asc = 'asc';
    case Desc = 'desc';

    // Getting all tags
    public static function tags(): array
    {
        return [
            self::Php->message(),
            self::Ruby->message(),
            self::Java->message(),
            self::Javascript->message(),
            self::Bash->message(),
        ];
    }

    // Getting all sortable fields
    public static function sortableFields(): array
    {
        return [
            self::Title->message(),
            self::Content->message(),
        ];
    }

    // Getting all sort order options
    public static function sortOrderOptions(): array
    {
        return [
            self::Asc->message(),
            self::Desc->message(),
        ];
    }

    // Getting min and max items per page
    public static function itemsPerPage(): array
    {
        return [
            'min' => (int) self::MinItemsPerPage->message(),
            'max' => (int) self::MaxItemsPerPage->message(),
        ];
    }
}

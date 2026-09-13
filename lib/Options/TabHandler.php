<?php

namespace Base\Module\Options;

use Base\Module\Service\Options\Tab;
use Bitrix\Main\Localization\Loc;

class TabHandler implements Tab
{
    public static function getId(): string
    {
        return 'handlers';
    }

    public static function getName(): string
    {
        return Loc::getMessage('MODULE_TAB_HANDLERS_TITLE');
    }

    public static function getSort(): int
    {
        return 20000;
    }
}
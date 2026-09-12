<?php

namespace Base\Module\Options;

use Base\Module\Service\Options\Tab;
use Bitrix\Main\Localization\Loc;

class TabSmartProcessHandlers implements Tab
{
    public static function getId(): string
    {
        return 'smart_process_handlers';
    }

    public static function getName(): string
    {
        return Loc::getMessage('MODULE_TAB_SMART_PROCESS_HANDLERS_TITLE');
    }

    public static function getSort(): int
    {
        return 20100;
    }
}
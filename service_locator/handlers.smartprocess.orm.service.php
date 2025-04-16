<?php

defined('B_PROLOG_INCLUDED') || die;

return [
    'base.module.handlers.smartprocess.orm.service' => [
        'className' => Base\Module\Src\Handlers\HandlersSmartProcessOrmService::class,
        'constructorParams' => [
            'base.module'
        ],
    ],
];

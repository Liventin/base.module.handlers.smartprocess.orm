<?php

namespace Base\Module\Service\Handlers;


use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class HandlerSmartProcessOrm
{
    public function __construct(
        public readonly string $smartProcessName,
        public readonly string $ormEvent,
        public readonly int $sort = 100,
    ) {
    }
}

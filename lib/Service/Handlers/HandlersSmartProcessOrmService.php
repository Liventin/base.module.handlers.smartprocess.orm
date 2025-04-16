<?php

namespace Base\Module\Service\Handlers;


interface HandlersSmartProcessOrmService
{
    public const SERVICE_CODE = 'base.module.handlers.smartprocess.orm.service';

    public function setHandlers(array $handlers): self;

    public function install(): void;

    public function unInstall(bool $saveData): void;

    public function reInstall(): void;
}

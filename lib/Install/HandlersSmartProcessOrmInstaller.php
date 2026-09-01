<?php

namespace Base\Module\Install;

use Base\Module\Install\Interface\Install;
use Base\Module\Install\Interface\UnInstall;
use Base\Module\Install\Interface\ReInstall;
use Base\Module\Service\Container;
use Base\Module\Exception\ModuleException;
use Base\Module\Service\Handlers\HandlersSmartProcessOrmService as ISmartProcessOrmHandlersService;
use Base\Module\Service\Tool\ClassList;

class HandlersSmartProcessOrmInstaller implements Install, UnInstall, ReInstall
{
    /**
     * @return array
     * @throws ModuleException
     */
    private function getHandlers(): array
    {
        /** @var ClassList $classList */
        $classList = Container::get(ClassList::SERVICE_CODE);
        return $classList->setSubClassesFilter([])->getFromLib('Handlers');
    }

    /**
     * @throws ModuleException
     */
    public function install(): void
    {
        /** @var ISmartProcessOrmHandlersService $handlersService */
        $handlersService = Container::get(ISmartProcessOrmHandlersService::SERVICE_CODE);
        $handlersService->setHandlers($this->getHandlers())->install();
    }

    /**
     * @throws ModuleException
     */
    public function unInstall(bool $saveData): void
    {
        /** @var ISmartProcessOrmHandlersService $handlersService */
        $handlersService = Container::get(ISmartProcessOrmHandlersService::SERVICE_CODE);
        $handlersService->setHandlers($this->getHandlers())->unInstall($saveData);
    }

    /**
     * @throws ModuleException
     */
    public function reInstall(): void
    {
        /** @var ISmartProcessOrmHandlersService $handlersService */
        $handlersService = Container::get(ISmartProcessOrmHandlersService::SERVICE_CODE);
        $handlersService->setHandlers($this->getHandlers())->reInstall();
    }

    public function getInstallSort(): int
    {
        return 1000;
    }

    public function getUnInstallSort(): int
    {
        return 1000;
    }

    public function getReInstallSort(): int
    {
        return 1000;
    }
}

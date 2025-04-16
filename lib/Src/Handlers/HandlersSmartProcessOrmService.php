<?php

namespace Base\Module\Src\Handlers;

use Bitrix\Crm\Model\Dynamic\TypeTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use ReflectionClass;
use ReflectionException;
use Base\Module\Service\Handlers\HandlersSmartProcessOrmService as ISmartProcessOrmHandlersService;
use Base\Module\Service\LazyService;

#[LazyService(serviceCode: ISmartProcessOrmHandlersService::SERVICE_CODE, constructorParams: ['moduleId' => LazyService::MODULE_ID])]
class HandlersSmartProcessOrmService implements ISmartProcessOrmHandlersService
{
    private string $moduleId;
    private array $handlers = [];
    private const OPTION_NAME = 'smart_process_orm_handlers';

    public function __construct(string $moduleId)
    {
        $this->moduleId = $moduleId;
    }

    /**
     * @throws ReflectionException
     * @throws LoaderException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function setHandlers(array $handlers): self
    {
        Loader::requireModule('crm');

        $smartProcesses = $this->getDynamicsList();
        $smartProcessMap = [];
        foreach ($smartProcesses as $smartProcess) {
            $smartProcessMap[$smartProcess['NAME']] = $smartProcess['ID'];
        }

        $this->handlers = [];
        foreach ($handlers as $className) {
            $reflection = new ReflectionClass($className);
            foreach ($reflection->getMethods() as $method) {
                if (!$method->isStatic()) {
                    continue;
                }

                $attributes = $method->getAttributes();
                foreach ($attributes as $attribute) {
                    $handler = $attribute->newInstance();
                    if (!isset(
                        $handler->smartProcessName, $handler->ormEvent, $handler->sort,
                        $smartProcessMap[$handler->smartProcessName]
                    )) {
                        continue;
                    }

                    $entityTypeId = $smartProcessMap[$handler->smartProcessName];
                    $eventName = "\crm_items_$entityTypeId::$handler->ormEvent";

                    $this->handlers[] = [
                        'smartProcessName' => $handler->smartProcessName,
                        'ormEvent' => $handler->ormEvent,
                        'eventName' => $eventName,
                        'sort' => $handler->sort,
                        'class' => $className,
                        'method' => $method->getName(),
                    ];
                }
            }
        }

        usort($this->handlers, static fn($a, $b) => $a['sort'] <=> $b['sort']);
        return $this;
    }

    /**
     * @return void
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     */
    public function install(): void
    {
        $this->unInstall(false);

        $eventManager = EventManager::getInstance();

        $newHandlers = [];
        foreach ($this->handlers as $handler) {
            $handlerKey = $this->getHandlerKey($handler);

            $eventManager->registerEventHandler(
                '',
                $handler['eventName'],
                $this->moduleId,
                $handler['class'],
                $handler['method'],
                $handler['sort']
            );

            $newHandlers[$handlerKey] = $handler;
        }

        Option::set($this->moduleId, self::OPTION_NAME, serialize($newHandlers));
    }

    /**
     * @throws SystemException
     */
    public function unInstall(bool $saveData): void
    {
        $eventManager = EventManager::getInstance();
        $handlers = $this->getStoredHandlers();

        foreach ($handlers as $handler) {
            $eventManager->unRegisterEventHandler(
                '',
                $handler['eventName'],
                $this->moduleId,
                $handler['class'],
                $handler['method']
            );
        }

        Option::delete($this->moduleId, ['name' => self::OPTION_NAME]);
    }

    /**
     * @throws ArgumentOutOfRangeException
     * @throws SystemException
     */
    public function reInstall(): void
    {
        $this->install();
    }

    /**
     * @throws SystemException
     * @throws ArgumentException
     */
    private function getDynamicsList(): array
    {
        return TypeTable::query()
            ->addSelect('ID')
            ->addSelect('NAME')
            ->addSelect('ENTITY_TYPE_ID')
            ->setCacheTtl(86400)
            ->fetchAll();
    }

    private function getStoredHandlers(): array
    {
        $serialized = Option::get($this->moduleId, self::OPTION_NAME);
        if (empty($serialized)) {
            return [];
        }

        $handlers = unserialize($serialized, ['allowed_classes' => false]);
        return is_array($handlers) ? $handlers : [];
    }

    private function getHandlerKey(array $handler): string
    {
        return md5(
            $handler['smartProcessName'] . '|' .
            $handler['ormEvent'] . '|' .
            $handler['class'] . '|' .
            $handler['method']
        );
    }
}

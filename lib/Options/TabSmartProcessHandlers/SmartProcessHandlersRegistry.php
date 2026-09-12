<?php

namespace Base\Module\Options\TabSmartProcessHandlers;

use Base\Module\Exception\ModuleException;
use Base\Module\Options\TabSmartProcessHandlers;
use Base\Module\Service\Container;
use Base\Module\Service\Options\Option;
use Base\Module\Service\Options\OptionsService;
use Base\Module\Service\Handlers\HandlersSmartProcessOrmService as ISmartProcessOrmHandlersService;
use Base\Module\Src\Options\Providers\TableProvider;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;

class SmartProcessHandlersRegistry implements Option
{
    public static function getId(): string
    {
        return 'smart_process_handlers_registry';
    }

    public static function getName(): string
    {
        return Loc::getMessage('MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_TITLE');
    }

    public static function getType(): string
    {
        return 'table';
    }

    public static function getTabId(): string
    {
        return TabSmartProcessHandlers::getId();
    }

    public static function getSort(): int
    {
        return 100;
    }

    /**
     * @return array
     * @throws ModuleException
     */
    public static function getParams(): array
    {
        /** @var OptionsService $srvOptions */
        $srvOptions = Container::get(OptionsService::SERVICE_CODE);
        /** @var TableProvider $provider */
        $provider = $srvOptions->getProvider(self::getType());

        if (!$provider) {
            return [];
        }

        return $provider
            ->setColumns([
                Loc::getMessage('MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_COL_SMART_PROCESS'),
                Loc::getMessage('MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_COL_EVENT'),
                Loc::getMessage('MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_COL_CLASS'),
                Loc::getMessage('MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_COL_METHOD'),
                Loc::getMessage('MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_COL_STATUS'),
                Loc::getMessage('MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_COL_SORT'),
            ])
            ->setChildColumns([
                Loc::getMessage('MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_COL_MODULE'),
                Loc::getMessage('MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_COL_CLASS'),
                Loc::getMessage('MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_COL_METHOD'),
                Loc::getMessage('MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_COL_SORT'),
            ])
            ->setRows(self::collectRows())
            ->setEmpty(Loc::getMessage('MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_EMPTY'))
            ->setExpandLabel(Loc::getMessage('MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_EXPAND'))
            ->getParamsToArray();
    }

    /**
     * @return array
     */
    private static function collectRows(): array
    {
        $rows = [];

        /** @var ISmartProcessOrmHandlersService $handlersService */
        $handlersService = self::getHandlersService();
        if ($handlersService === null) {
            return $rows;
        }

        $moduleId = $handlersService->getModuleId();
        $handlers = $handlersService->getStoredHandlers();
        if (empty($handlers)) {
            return $rows;
        }

        $eventManager = EventManager::getInstance();
        $eventManager->clearLoadedHandlers();

        $registry = [];
        foreach ($handlers as $handler) {
            $key = self::getEventKey($handler);
            if (!isset($registry[$key])) {
                $registry[$key] = $eventManager->findEventHandlers('', $handler['eventName']);
            }
        }

        foreach ($handlers as $handler) {
            $key = self::getEventKey($handler);
            $eventHandlers = $registry[$key] ?? [];
            $selfRegistered = self::isSelfRegistered($eventHandlers, $handler, $moduleId);

            $rows[] = [
                'cells' => [
                    $handler['smartProcessName'],
                    $handler['ormEvent'],
                    $handler['class'],
                    $handler['method'],
                    ['text' => Loc::getMessage(
                            $selfRegistered
                                ? 'MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_STATUS_YES'
                                : 'MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_STATUS_NO'
                        ), 'status' => $selfRegistered ? 'ok' : 'no'],
                    (int)$handler['sort'],
                ],
                'highlight' => true,
                'children' => self::collectEventRows($eventHandlers, $moduleId),
            ];
        }

        return $rows;
    }

    /**
     * @param array $eventHandlers
     * @param string $moduleId
     * @return array
     */
    private static function collectEventRows(array $eventHandlers, string $moduleId): array
    {
        $rows = [];

        foreach ($eventHandlers as $item) {
            $isSelf = (string)($item['TO_MODULE_ID'] ?? '') === $moduleId;
            $class = (string)($item['TO_CLASS'] ?? '');
            $method = (string)($item['TO_METHOD'] ?? '');

            if (!empty($item['TO_PATH'] ?? '')) {
                $class = (string)$item['TO_PATH'];
            } elseif ($class === '' && $method === '') {
                $name = (string)($item['TO_NAME'] ?? '');
                if ($name !== '') {
                    $parts = explode('::', $name, 2);
                    $class = $parts[0];
                    $method = $parts[1] ?? '';
                } else {
                    $class = Loc::getMessage('MODULE_OPTION_SMART_PROCESS_HANDLERS_REGISTRY_INCLUDE_MODULE');
                }
            }

            $rows[] = [
                'cells' => [
                    (string)($item['TO_MODULE_ID'] ?? ''),
                    $class,
                    $method,
                    (int)($item['SORT'] ?? 0),
                ],
                'highlight' => $isSelf,
            ];
        }

        return $rows;
    }

    /**
     * @param array $eventHandlers
     * @param array $handler
     * @param string $moduleId
     * @return bool
     */
    private static function isSelfRegistered(array $eventHandlers, array $handler, string $moduleId): bool
    {
        foreach ($eventHandlers as $item) {
            if ((string)($item['TO_MODULE_ID'] ?? '') !== $moduleId) {
                continue;
            }
            if ((string)($item['TO_CLASS'] ?? '') === $handler['class'] &&
                (string)($item['TO_METHOD'] ?? '') === $handler['method']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $handler
     * @return string
     */
    private static function getEventKey(array $handler): string
    {
        return (string)$handler['eventName'];
    }

    /**
     * @return ISmartProcessOrmHandlersService|null
     */
    private static function getHandlersService(): ?ISmartProcessOrmHandlersService
    {
        try {
            if (!Container::has(ISmartProcessOrmHandlersService::SERVICE_CODE)) {
                return null;
            }
            return Container::get(ISmartProcessOrmHandlersService::SERVICE_CODE);
        } catch (ModuleException) {
            return null;
        }
    }
}
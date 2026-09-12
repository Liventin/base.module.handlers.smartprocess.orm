# base.module.handlers.smartprocess.orm

<table>
<tr>
<td>
<a href="https://github.com/Liventin/base.module">Bitrix Base Module</a>
</td>
</tr>
</table>

install | update

```
"require": {
    "liventin/base.module.handlers.smartprocess.orm": "^1.0.0"
}
```
redirect (optional)
```
"extra": {
  "service-redirect": {
    "liventin/base.module.handlers.smartprocess.orm": "module.name",
  }
}
```

PhpStorm Live Template
```php
<?php

namespace ${MODULE_PROVIDER_CAMMAL_CASE}\\${MODULE_CODE_CAMMAL_CASE}\Handlers;

use ${MODULE_PROVIDER_CAMMAL_CASE}\\${MODULE_CODE_CAMMAL_CASE}\Service\Handlers\HandlerSmartProcessOrm;
use Bitrix\Main\Event;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Objectify\EntityObject;

class HandlerSmartProcessOrmExample
{
    #[HandlerSmartProcessOrm('SmartProcessName', DataManager::EVENT_ON_BEFORE_UPDATE)]
    public static function checkStatuses(Event ${DS}event): void
    {
        /** @var EntityObject ${DS}entityObject */
        ${DS}entityObject = ${DS}event->getParameter('object');
    }
}
```

## Вкладка «Смарт ORM обработчики» в опциях модуля

Вместе с пакетом `base.module.options.provider.table` при установке в опции модуля
добавляется вкладка **«Смарт ORM обработчики»** с реестром обработчиков смарт-процессов:

- строка на каждый обработчик модуля: смарт-процесс, событие, класс, метод, статус
  (установлен/не установлен), приоритет;
- по клику раскрывается список **всех** зарегистрированных в системе обработчиков
  этого события (включая чужие модули), отсортированных по приоритету;
- собственные обработчики модуля визуально подсвечены.

```json
"require": {
    "liventin/base.module.handlers.smartprocess.orm": "^1.0.0",
    "liventin/base.module.options.provider.table": "^1.0.0"
}
```
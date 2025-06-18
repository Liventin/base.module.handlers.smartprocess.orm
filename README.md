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
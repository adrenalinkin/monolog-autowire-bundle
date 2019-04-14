Monolog Autowire Bundle [![In English](https://img.shields.io/badge/Switch_To-English-green.svg?style=flat-square)](./README.md)
=======================

[![Latest Stable Version](https://poser.pugx.org/adrenalinkin/monolog-autowire-bundle/v/stable)](https://packagist.org/packages/adrenalinkin/monolog-autowire-bundle)
[![Total Downloads](https://poser.pugx.org/adrenalinkin/monolog-autowire-bundle/downloads)](https://packagist.org/packages/adrenalinkin/monolog-autowire-bundle)

[![knpbundles.com](http://knpbundles.com/adrenalinkin/monolog-autowire-bundle/badge-short)](http://knpbundles.com/adrenalinkin/monolog-autowire-bundle)

Введение
--------

Бандл предоставляет доступ ко всем зарегистрированным в `MonologBundle` каналам посредством `LoggerHandler`.
В случае если запрашиваемого канала не существует - будет выбран запасной `logger`. 
В качестве запасного `logger` будет использован сервис, на который ссылается `@Psr\Log\LoggerInterface`. 
Если запасной `logger` не зарегистрирован в контейнере сервисов, то будет возвращен экземпляр `Psr\Log\NullLogger`.

**Важно:** Бандл будет исправно работать при отсутствии `MonologBundle` в проекте. 
В этом случае `LoggerHandler` будет всегда возвращать запасное значение.

Установка
---------

### Шаг 1: Загрузка бандла

Откройте консоль и, перейдя в директорию проекта, выполните следующую команду для загрузки наиболее подходящей
стабильной версии этого бандла:
```bash
    composer require adrenalinkin/monolog-autowire-bundle
```
*Эта команда подразумевает что [Composer](https://getcomposer.org) установлен и доступен глобально.*

### Шаг 2: Подключение бандла

После включите бандл добавив его в список зарегистрированных бандлов в `app/AppKernel.php` файл вашего проекта:

```php
<?php declare(strict_types=1);
// app/AppKernel.php

class AppKernel extends Kernel
{
    // ...

    public function registerBundles()
    {
        $bundles = [
            // ...

            new Linkin\Bundle\MonologAutowireBundle\LinkinMonologAutowireBundle(),
        ];

        return $bundles;
    }

    // ...
}
```

Конфигурация
------------

Чтобы начать использовать бандл не требуется предварительной конфигурации.

Использование
-------------

Допустим в нашем проекте есть следующая конфигурация `MonologBundle`:

```yaml
monolog:
    handlers:
        doctrine:
            type:   stream
            path:   "%kernel.logs_dir%/%kernel.environment%.doctrine.log"
            level:  info
            channels:
                - "doctrine"

        acme:
            type:   stream
            path:   "%kernel.logs_dir%/%kernel.environment%.acme_channel.log"
            level:  info
            channels:
                - "acme_channel"
```

Доступ к нужным каналам логирования с использованием механизма `autowire` теперь доступен через `LoggerHandler`:

```php
<?php declare(strict_types=1);

use Linkin\Bundle\MonologAutowireBundle\Handler\LoggerHandler;

class AcmeLoggerAware
{
    /**
     * @var LoggerHandler
     */
    private $loggerHandler;

    /**
     * @param LoggerHandler $loggerHandler
     */
    public function __construct(LoggerHandler $loggerHandler) 
    {
        $this->loggerHandler = $loggerHandler;
    }
    
    public function doSome(): void
    {
        $this->loggerHandler->getLogger('acme_channel')->info('INFO into "acme_channel" channel');
        $this->loggerHandler->getLogger('doctrine')->info('INFO into "doctrine" channel');
        $this->loggerHandler->getLogger()->info('INFO into Fallback or into NullLogger');
    }
}
```

Лицензия
--------

[![license](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](./LICENSE)

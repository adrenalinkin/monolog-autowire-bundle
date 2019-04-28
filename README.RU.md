Monolog Autowire Bundle [![In English](https://img.shields.io/badge/Switch_To-English-green.svg?style=flat-square)](./README.md)
=======================

[![Latest Stable Version](https://poser.pugx.org/adrenalinkin/monolog-autowire-bundle/v/stable)](https://packagist.org/packages/adrenalinkin/monolog-autowire-bundle)
[![Total Downloads](https://poser.pugx.org/adrenalinkin/monolog-autowire-bundle/downloads)](https://packagist.org/packages/adrenalinkin/monolog-autowire-bundle)

[![knpbundles.com](http://knpbundles.com/adrenalinkin/monolog-autowire-bundle/badge-short)](http://knpbundles.com/adrenalinkin/monolog-autowire-bundle)

Введение
--------

Бандл предоставляет возможность подключать зарегистрированные в `MonologBundle` логгеры
посредством стандартного механизма `autowire`.
Цель достигается благодаря автогенерируемым классам логгеров. Каждый класс декорирует один объект одного
зарегистрированного `monolog` канала.

Также доступен второй способ достижения цели - при помощи управляющего класса `LoggerCollection`.
В случае если запрашиваемого канала не существует - будет выбран запасной `logger`. 
В качестве запасного `logger` будет использован сервис, на который ссылается `@Psr\Log\LoggerInterface`. 
Если запасной `logger` не зарегистрирован в контейнере сервисов, то будет возвращен экземпляр `Psr\Log\NullLogger`.

**Важно:** Бандл будет исправно работать при отсутствии `MonologBundle` в проекте. 
В этом случае `LoggerCollection` будет всегда возвращать запасное значение.

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
Все параметры имеют значения по умолчанию:

```yaml
linkin_monolog_autowire:
    # директория где будут созданы классы-декораторы логгеров
    loggers_dir:        '%kernel.project_dir%/var/loggers'
    # путь к файлу шаблона декоратора
    decorator_template: 'ChannelLogger.php.dist'
```

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
            path:   "%kernel.logs_dir%/%kernel.environment%.acme_log.log"
            level:  info
            channels:
                - "acme_log"
```

### Использование через автогенерируемые логгеры

Названия классов генерируются на основе имени канала. Все не буквенно-числовые значения удаляются,
а название приводится к формату `CamelCase`. Все классы начинаются с `Channel` и заканчиваются на `Logger`.

```php
<?php declare(strict_types=1);

use Linkin\Bundle\MonologAutowireBundle\Logger\ChannelAcmeLogLogger;
use Linkin\Bundle\MonologAutowireBundle\Logger\ChannelDoctrineLogger;
use Psr\Log\LoggerInterface;

class AcmeLoggerAware
{
    /**
     * @var ChannelDoctrineLogger
     */
    private $acmeLogLogger;

    /**
     * @var ChannelDoctrineLogger
     */
    private $doctrineLogger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ChannelAcmeLogLogger $acmeLogLogger
     * @param ChannelDoctrineLogger $doctrineLogger
     * @param LoggerInterface $logger
     */
    public function __construct(
        ChannelAcmeLogLogger $acmeLogLogger,
        ChannelDoctrineLogger $doctrineLogger,
        LoggerInterface $logger
    ) {
        $this->acmeLogLogger = $acmeLogLogger;
        $this->doctrineLogger = $doctrineLogger;
        $this->logger = $logger;
    }
    
    public function doSome(): void
    {
        $this->acmeLogLogger->info('INFO into "acme_log" channel');
        $this->doctrineLogger->info('INFO into "doctrine" channel');
        $this->logger->info('INFO into Fallback or into NullLogger');
    }
}
```

### Использование через коллекцию логгеров

```php
<?php declare(strict_types=1);

use Linkin\Bundle\MonologAutowireBundle\Collection\LoggerCollection;
use Psr\Log\LoggerInterface;

class AcmeLoggerAware
{
    /**
     * @var LoggerInterface
     */
    private $acmeLogLogger;

    /**
     * @var LoggerInterface
     */
    private $doctrineLogger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerCollection $loggerCollection
     */
    public function __construct(LoggerCollection $loggerCollection) 
    {
        $this->acmeLogLogger = $loggerCollection->getLogger('acme_log');
        $this->doctrineLogger = $loggerCollection->getLogger('doctrine');
        $this->logger = $loggerCollection->getLogger();
    }
    
    public function doSome(): void
    {
        $this->acmeLogLogger->info('INFO into "acme_log" channel');
        $this->doctrineLogger->info('INFO into "doctrine" channel');
        $this->logger->info('INFO into Fallback or into NullLogger');
    }
}
```

Лицензия
--------

[![license](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](./LICENSE)

Monolog Autowire Bundle [![На Русском](https://img.shields.io/badge/Перейти_на-Русский-green.svg?style=flat-square)](./README.RU.md)
=======================

[![Latest Stable Version](https://poser.pugx.org/adrenalinkin/monolog-autowire-bundle/v/stable)](https://packagist.org/packages/adrenalinkin/monolog-autowire-bundle)
[![Total Downloads](https://poser.pugx.org/adrenalinkin/monolog-autowire-bundle/downloads)](https://packagist.org/packages/adrenalinkin/monolog-autowire-bundle)

[![knpbundles.com](http://knpbundles.com/adrenalinkin/monolog-autowire-bundle/badge-short)](http://knpbundles.com/adrenalinkin/monolog-autowire-bundle)

Introduction
------------

The bundle provides access to all channels registered in `MonologBundle` via` LoggerHandler`.
If the requested channel does not exist - will be selected fallback `logger`.
As fallback `logger`  will be used service, which referenced by `@Psr\Log\LoggerInterface`.
In that case where `logger` was not registered in service container will be returned instance of `Psr\Log\NullLogger`.

**Important:** Bundle will work properly in the absence of `MonologBundle` in the project.
In that case `LoggerHandler` will always return a fallback value.

Installation
-----------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download
the latest stable version of this bundle:
```bash
    composer require adrenalinkin/monolog-autowire-bundle
```
*is command requires you to have [Composer](https://getcomposer.org) install globally.*

### Step 2: Enable the Bundle

Then, enable the bundle by updating your `app/AppKernel.php` file to enable the bundle:

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

Configuration
------------

To start using bundle you don't need to define some additional configuration.

Usage
-----

Suppose our project has the following configuration `MonologBundle`:

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

Access to the required logging channels using the `autowire` mechanism is now available via `LoggerHandler`:

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

License
-------

[![license](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](./LICENSE)

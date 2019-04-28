<?php

declare(strict_types=1);

/*
 * This file is part of the MonologAutowireBundle package.
 *
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\MonologAutowireBundle;

use Linkin\Bundle\MonologAutowireBundle\Cache\LoggerClassCache;
use Linkin\Bundle\MonologAutowireBundle\DependencyInjection\Compiler\LoggerAutowireCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use function spl_autoload_unregister;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class LinkinMonologAutowireBundle extends Bundle
{
    /**
     * @var callable|null
     */
    private $autoloader = null;

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new LoggerAutowireCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 255);
    }

    public function boot()
    {
        if (!$this->container->hasParameter('linkin_monolog_autowire.loggers_dir')) {
            return;
        }

        $loggersDir = $this->container->getParameter('linkin_monolog_autowire.loggers_dir');

        $this->autoloader = LoggerClassCache::register($loggersDir);

        parent::boot();
    }

    public function shutdown()
    {
        if ($this->autoloader !== null) {
            spl_autoload_unregister($this->autoloader);
            $this->autoloader = null;
        }

        parent::shutdown();
    }
}

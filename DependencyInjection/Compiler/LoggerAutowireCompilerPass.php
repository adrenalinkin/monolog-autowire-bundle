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

namespace Linkin\Bundle\MonologAutowireBundle\DependencyInjection\Compiler;

use Linkin\Bundle\MonologAutowireBundle\Cache\LoggerClassCache;
use Linkin\Bundle\MonologAutowireBundle\Collection\LoggerCollection;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use function str_replace;
use function strpos;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class LoggerAutowireCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(LoggerCollection::class)) {
            return;
        }

        $loggersDir = $container->getParameter('linkin_monolog_autowire.loggers_dir');

        $loggerChannels = [];

        $loggerCache = new LoggerClassCache($loggersDir);
        $loggerCache->clear();

        foreach ($container->getDefinitions() as $id => $definition) {
            if (strpos($id, 'monolog.logger.') !== 0) {
                continue;
            }

            $channelName = str_replace('monolog.logger.', '', $id);

            $loggerChannels[$channelName] = $definition;

            $loggerFullClassName = $loggerCache->generateClass($channelName);

            $container->register($loggerFullClassName, $loggerFullClassName)->addArgument(new Reference($id));
        }

        $container->getDefinition(LoggerCollection::class)->replaceArgument(0, $loggerChannels);
    }
}

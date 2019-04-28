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
        $loggerTemplate = $container->getParameter('linkin_monolog_autowire.decorator_template');

        $loggerCache = new LoggerClassCache($loggersDir, $loggerTemplate);
        $loggerCache->clear();

        $loggerChannels = [];

        foreach ($container->findTaggedServiceIds('monolog.logger') as $id => $tags) {
            foreach ($tags as $tag) {
                if (empty($tag['channel'])) {
                    continue;
                }

                $resolvedChannel = $container->getParameterBag()->resolveValue($tag['channel']);

                $loggerChannels[$resolvedChannel] = $this->getLoggerReference($resolvedChannel);
            }
        }

        foreach ($container->getParameter('monolog.additional_channels') as $channelName) {
            $loggerChannels[$channelName] = $this->getLoggerReference($channelName);
        }

        foreach ($loggerChannels as $channelName => $serviceReference) {
            $loggerFullClassName = $loggerCache->generateClass($channelName);
            $container->register($loggerFullClassName, $loggerFullClassName)->addArgument($serviceReference);
        }

        $container->getDefinition(LoggerCollection::class)->replaceArgument(0, $loggerChannels);
    }

    /**
     * @param string $channelName
     *
     * @return Reference
     */
    private function getLoggerReference(string $channelName): Reference
    {
        return new Reference('monolog.logger.' . $channelName);
    }
}

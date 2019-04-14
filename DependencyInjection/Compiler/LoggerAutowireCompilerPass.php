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

use Linkin\Bundle\MonologAutowireBundle\Handler\LoggerHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
        if (!$container->hasDefinition(LoggerHandler::class)) {
            return;
        }

        $loggerChannels = [];

        foreach ($container->getDefinitions() as $id => $definition) {
            if (strpos($id, 'monolog.logger.') !== 0) {
                continue;
            }

            $channelName = str_replace('monolog.logger.', '', $id);

            $loggerChannels[$channelName] = $definition;
        }

        $container->getDefinition(LoggerHandler::class)->replaceArgument(0, $loggerChannels);
    }
}

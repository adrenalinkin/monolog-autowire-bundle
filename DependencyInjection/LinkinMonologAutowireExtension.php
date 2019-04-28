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

namespace Linkin\Bundle\MonologAutowireBundle\DependencyInjection;

use Linkin\Bundle\MonologAutowireBundle\Cache\LoggerClassCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class LinkinMonologAutowireExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $projectDir = $container->getParameter('kernel.project_dir');

        $configuration = new Configuration($projectDir);
        $config = $this->processConfiguration($configuration, $configs);

        $loggerDir = $config['loggers_dir'];

        $container->setParameter('linkin_monolog_autowire.decorator_template', $config['decorator_template']);
        $container->setParameter('linkin_monolog_autowire.loggers_dir', $loggerDir);

        LoggerClassCache::register($loggerDir);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }
}

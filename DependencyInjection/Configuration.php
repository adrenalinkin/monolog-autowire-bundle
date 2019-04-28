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

use Closure;
use Linkin\Bundle\MonologAutowireBundle\Cache\LoggerClassCache;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use function file_exists;
use function file_get_contents;
use function sprintf;
use function strpos;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var string
     */
    private $projectDir;

    /**
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('linkin_monolog_autowire');

        $rootNode
            ->children()
                ->scalarNode('loggers_dir')
                    ->info('Directory where should be stored auto-generated loggers decorators')
                    ->cannotBeEmpty()
                    ->defaultValue($this->projectDir . '/var/loggers')
                ->end()
                ->scalarNode('decorator_template')
                    ->info('Path to loggers decorator template')
                    ->cannotBeEmpty()
                    ->defaultValue(__DIR__ . '/../Cache/ChannelLogger.php.dist')
                    ->validate()
                        ->always($this->validationForDecoratorTemplate())
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @return Closure
     */
    private function validationForDecoratorTemplate(): Closure
    {
        return function ($pathToTemplate) {
            if (!file_exists($pathToTemplate)) {
                throw new InvalidConfigurationException(sprintf(
                    'Parameter "decorator_template" contains incorrect data. File "%s" not found',
                    $pathToTemplate
                ));
            }

            $templateContent = file_get_contents($pathToTemplate);

            if (strpos($templateContent, LoggerClassCache::LOGGER_PLACEHOLDER_NAMESPACE) === false) {
                throw new InvalidConfigurationException(sprintf(
                    'Parameter "decorator_template" contains incorrect data. Template should contain "%s" placeholder',
                    LoggerClassCache::LOGGER_PLACEHOLDER_NAMESPACE
                ));
            }

            if (strpos($templateContent, LoggerClassCache::LOGGER_PLACEHOLDER_CLASS_NAME) === false) {
                throw new InvalidConfigurationException(sprintf(
                    'Parameter "decorator_template" contains incorrect data. Template should contain "%s" placeholder',
                    LoggerClassCache::LOGGER_PLACEHOLDER_CLASS_NAME
                ));
            }

            return $pathToTemplate;
        };
    }
}

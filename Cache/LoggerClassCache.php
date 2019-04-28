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

namespace Linkin\Bundle\MonologAutowireBundle\Cache;

use Closure;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use function end;
use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function preg_replace;
use function spl_autoload_register;
use function sprintf;
use function str_replace;
use function ucfirst;
use function ucwords;
use function unlink;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class LoggerClassCache
{
    public const LOGGER_PLACEHOLDER_CLASS_NAME = '__LINKIN_CHANNEL_LOGGER_CLASS_NAME__';
    public const LOGGER_PLACEHOLDER_NAMESPACE = '__LINKIN_CHANNEL_LOGGER_NAMESPACE__';

    private const LOGGER_CLASS_POSTFIX = 'Logger';
    private const LOGGER_CLASS_PREFIX = 'Channel';
    private const LOGGER_NAMESPACE = 'Linkin\\Bundle\\MonologAutowireBundle\\Logger';

    /**
     * @var string
     */
    private $loggersDir;

    /**
     * @var string
     */
    private $loggerTemplate;

    /**
     * @param string $loggersDir
     * @param string $loggerTemplate
     */
    public function __construct(string $loggersDir, string $loggerTemplate)
    {
        $this->loggersDir = $loggersDir;
        $this->loggerTemplate = file_get_contents($loggerTemplate);
    }

    /**
     * Remove all already exist logger classes
     */
    public function clear(): void
    {
        $finder = new Finder();
        $finder->files()->in($this->getLoggerDir())->name('*.php');

        foreach ($finder as $file) {
            if ($file instanceof SplFileInfo) {
                unlink((string) $file);
            }
        }
    }

    /**
     * Generates new logger decorator and returns FQCN
     *
     * @param string $channelName
     *
     * @return string
     */
    public function generateClass(string $channelName): string
    {
        $loggerClassName = $this->generateClassNameByChannel($channelName);
        $loggerClassContent = str_replace(
            [self::LOGGER_PLACEHOLDER_CLASS_NAME, self::LOGGER_PLACEHOLDER_NAMESPACE],
            [$loggerClassName, self::LOGGER_NAMESPACE],
            $this->loggerTemplate
        );

        file_put_contents(sprintf('%s/%s.php', $this->getLoggerDir(), $loggerClassName), $loggerClassContent);

        return self::LOGGER_NAMESPACE . '\\' . $loggerClassName;
    }

    /**
     * @param string $loggersDir
     *
     * @return Closure
     */
    public static function register(string $loggersDir): Closure
    {
        $closure = function ($className) use ($loggersDir) {
            $explodedClassName = explode('\\', $className);
            $fileName = end($explodedClassName);
            $absolutePath = sprintf('%s/%s.php', $loggersDir, $fileName);

            if (!file_exists($absolutePath)) {
                return;
            }

            require $absolutePath;
        };

        spl_autoload_register($closure);

        return $closure;
    }

    /**
     * @return string
     */
    private function getLoggerDir(): string
    {
        if (!is_dir($this->loggersDir)) {
            mkdir($this->loggersDir, 0777, true);
        }

        return $this->loggersDir;
    }

    /**
     * @param string $channelName
     *
     * @return string
     */
    private function generateClassNameByChannel(string $channelName): string
    {
        $spaceInsteadSymbols = preg_replace('/[^A-Za-z0-9]/', ' ', $channelName);
        $upperCaseWithSpaceInsteadSymbols = ucwords($spaceInsteadSymbols);
        $concatenatedString = str_replace(' ', '', $upperCaseWithSpaceInsteadSymbols);

        return self::LOGGER_CLASS_PREFIX . ucfirst($concatenatedString) . self::LOGGER_CLASS_POSTFIX;
    }
}

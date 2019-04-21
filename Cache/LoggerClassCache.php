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

use SplFileInfo;
use Symfony\Component\Finder\Finder;
use function file_get_contents;
use function file_put_contents;
use function preg_replace;
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
    private const PATH_TO_LOGGER = __DIR__ . '/../Logger';

    /**
     * @var string
     */
    private $loggerTemplate;

    /**
     * Remove all already exist logger classes
     */
    public function clear(): void
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in(self::PATH_TO_LOGGER)
            ->name('*.php')
        ;

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
        $loggerClassContent = sprintf($this->getLoggerTemplate(), $loggerClassName);

        file_put_contents(sprintf('%s/%s.php', self::PATH_TO_LOGGER, $loggerClassName), $loggerClassContent);

        return 'Linkin\\Bundle\\MonologAutowireBundle\\Logger\\' . $loggerClassName;
    }

    /**
     * @return string
     */
    private function getLoggerTemplate(): string
    {
        if (!$this->loggerTemplate) {
            $this->loggerTemplate = file_get_contents(self::PATH_TO_LOGGER . '/LoggerTemplate.php.txt');
        }

        return $this->loggerTemplate;
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

        return sprintf('Channel%sLogger', ucfirst($concatenatedString));
    }
}

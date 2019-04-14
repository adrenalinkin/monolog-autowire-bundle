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

namespace Linkin\Bundle\MonologAutowireBundle\Handler;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class LoggerHandler
{
    /**
     * @var LoggerInterface
     */
    private $fallbackLogger;

    /**
     * @var LoggerInterface[]
     */
    private $loggers;

    /**
     * @param LoggerInterface[] $loggers
     * @param LoggerInterface|null $fallbackLogger
     */
    public function __construct(array $loggers, ?LoggerInterface $fallbackLogger = null)
    {
        $this->loggers = $loggers;
        $this->fallbackLogger = $fallbackLogger ?? new NullLogger();
    }

    /**
     * @param string|null $name
     *
     * @return LoggerInterface
     */
    public function getLogger(?string $name = null): LoggerInterface
    {
        if (null === $name || empty($this->loggers[$name])) {
            return $this->fallbackLogger;
        }

        return $this->loggers[$name];
    }
}

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

use Linkin\Bundle\MonologAutowireBundle\DependencyInjection\Compiler\LoggerAutowireCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class LinkinMonologAutowireBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new LoggerAutowireCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -10);
    }
}

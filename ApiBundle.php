<?php

namespace ApiBundle;

use ModelBundle\DependencyInjection\Compiler\DispatcherCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ApiBundle
 * @package ApiBundle
 */
Class ApiBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DispatcherCompilerPass());
    }
}

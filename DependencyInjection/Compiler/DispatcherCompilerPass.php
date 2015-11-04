<?php

namespace ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class DispatcherCompilerPass implements CompilerPassInterface{

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('api.dispatcher')) {
            return;
        }

        $definition     = $container->findDefinition('api.dispatcher');
        $taggedServices = $container->findTaggedServiceIds('dispatcher');

        foreach($taggedServices as $id => $tags){

            $definition->addMethodCall(
                'addDispatcher' ,
                [new Reference($id)]
            );
        }
    }


}
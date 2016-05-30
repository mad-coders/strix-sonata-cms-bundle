<?php

namespace Strix\SonataCmsBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SonataAdminORMPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $templates = $container->getDefinition('sonata.admin.builder.orm_list')->getArgument(1);

        $templates['strix_raw'] = 'StrixSonataCmsBundle:Admin:list_raw.html.twig';

        $container->getDefinition('sonata.admin.builder.orm_list')->replaceArgument(1, $templates);
    }
}



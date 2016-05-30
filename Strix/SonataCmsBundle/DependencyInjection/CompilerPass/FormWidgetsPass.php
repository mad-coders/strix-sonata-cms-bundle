<?php

namespace Strix\SonataCmsBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FormWidgetsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $resources = $container->getParameter('twig.form.resources');

        $resources[] = 'StrixSonataCmsBundle:Form:widgets.html.twig';

        $container->setParameter('twig.form.resources', $resources);
    }
}
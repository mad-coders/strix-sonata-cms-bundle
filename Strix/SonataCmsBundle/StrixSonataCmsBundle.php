<?php

namespace Strix\SonataCmsBundle;

use Strix\SonataCmsBundle\DependencyInjection\CompilerPass\SonataAdminORMPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Strix\SonataCmsBundle\DependencyInjection\CompilerPass\FormWidgetsPass;
use Strix\SonataCmsBundle\DependencyInjection\CompilerPass\SonataAdminPass;

class StrixSonataCmsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FormWidgetsPass());

        if (class_exists('Sonata\AdminBundle\SonataAdminBundle')) {
            $container->addCompilerPass(new SonataAdminPass());
        }

        if (class_exists('Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle')) {
            $container->addCompilerPass(new SonataAdminORMPass());
        }
    }

}

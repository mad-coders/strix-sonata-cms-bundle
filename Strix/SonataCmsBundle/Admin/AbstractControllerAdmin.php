<?php

namespace Strix\SonataCmsBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class AbstractControllerAdmin extends AbstractStrixSonataCmsAdmin
{
    protected function configureFormFields(FormMapper $form)
    {
        $form->add('controllerName');
    }

    protected function configureListFields(ListMapper $list)
    {
        $list->addIdentifier('id')
            ->addIdentifier('controllerName');
    }

    public function toString($object)
    {
        return $object->getControllerName();
    }
}
<?php

namespace Strix\SonataCmsBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class AbstractCmsContextAdmin extends AbstractStrixSonataCmsAdmin
{
    protected function configureFormFields(FormMapper $form)
    {
        $form->add('contextName');
    }

    protected function configureListFields(ListMapper $list)
    {
        $list->addIdentifier('id')
            ->addIdentifier('contextName');
    }

    public function toString($object)
    {
        return $object->getContextName();
    }
}
<?php

namespace Strix\SonataCmsBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class AbstractTemplateAdmin extends AbstractStrixSonataCmsAdmin
{
    protected function configureFormFields(FormMapper $form)
    {
        $form->add('templateName');
    }

    protected function configureListFields(ListMapper $list)
    {
        $list->addIdentifier('id')
            ->addIdentifier('templateName');
    }

    public function toString($object)
    {
        return $object->getTemplateName();
    }
}
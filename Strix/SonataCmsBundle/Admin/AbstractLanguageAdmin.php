<?php

namespace Strix\SonataCmsBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

abstract class AbstractLanguageAdmin extends AbstractStrixSonataCmsAdmin
{
    protected $isSortable = true;

    protected $isEnabled = true;

    protected function configureFormFields(FormMapper $form)
    {
        $form->add('code')
            ->add('name')
            ->add('enabled', null, array('required' => false));
    }

    protected function configureListFields(ListMapper $list)
    {
        $list->addIdentifier('id');
        $this->addSortableControls($list);
        $this->addBooleanControls($list, 'enabled');
        $this->addBooleanControls($list, 'default', true);
        $list->add('name');
        $list->add('code');
    }

    public function toString($object)
    {
        return sprintf('%s (%s)', $object->getName(), $object->getCode());
    }

    public function createQuery($context = 'list')
    {
        $queryBuilder = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from($this->getClass(), 'c')
            ->addOrderBy('c.position', 'ASC');

        $query = new ProxyQuery($queryBuilder);

        return $query;
    }
}
<?php

namespace Strix\SonataCmsBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class AbstractTreeAdmin extends AbstractStrixSonataCmsAdmin
{
    protected $treeRootName = '-- TREE ROOT --';

    protected $titleField = null;

    protected $maxPerPage = 2500;

    protected $maxPageLinks = 25;

    public function upAction()
    {
        $repo = $this->getRepository();

        $repo->moveUp($this->getSubject());

        return new RedirectResponse($this->generateUrl('list'));
    }

    public function downAction()
    {
        $repo = $this->getRepository();

        $repo->moveDown($this->getSubject());

        return new RedirectResponse($this->generateUrl('list'));
    }

    protected function configureListFields(ListMapper $list)
    {
        $this->addListTreeControls($list);
        $list->addIdentifier('treeLevelTitle', 'strix_raw', array('label' => 'Title'));
    }

    protected function configureFormFields(FormMapper $form)
    {
        $this->addParentSelector($form);

        $form->add($this->titleField);
    }

    protected function addParentSelector(FormMapper $form)
    {
        $id = $this->getSubject()->getId();

        $form->add('parent', null, array(
                'property' => 'treeSelectLevelTitle',
                'required' => true,
                'query_builder' => function($repo) use ($id) {
                        $qb = $repo->createQueryBuilder('c');

                        if ($id) {
                            $qb
                                ->where('c.id <> :id')
                                ->setParameter('id', $id);
                        }
                        $qb
                            ->orderBy('c.root, c.left', 'ASC');

                        return $qb;
                    }
            ));
    }

    protected function addListTreeControls(ListMapper $list)
    {
        $list
            ->add('strix_sonata_cms_up', 'text', array('label' => 'Up', 'template' => 'StrixSonataCmsBundle:Admin:list_tree_up.html.twig'))
            ->add('strix_sonata_cms_down', 'text', array('label' => 'Down', 'template' => 'StrixSonataCmsBundle:Admin:list_tree_down.html.twig'));
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);

        $collection->add(
            $this->getNormalizedCode().'_up',
            $this->getRouterIdParameter() . '/up',
            array('_controller' => $this->code.':upAction')
        );
        $collection->add(
            $this->getNormalizedCode().'_down',
            $this->getRouterIdParameter() . '/down',
            array('_controller' => $this->code.':downAction')
        );
    }

    public function initialize()
    {
        parent::initialize();

        if ($this->titleField == null) {
            throw new \Exception('You need to set "titleField" property admin to title field of your entity');
        }

        $reflection = new \ReflectionClass($this->getClass());

        if (!$reflection->getParentClass() || $reflection->getParentClass()->getName() != 'Strix\SonataCmsBundle\Entity\AbstractStrixCmsTreeNode') {
            throw new \Exception('A tree entity should extend "Strix\SonataCmsBundle\Entity\AbstractStrixCmsTreeNode"');
        }

        $repo = $this->getRepository();

        $rootNodes = $repo->getRootNodes();

        if (count($rootNodes) == 0) {
            $className = $this->getClass();
            $root = new $className();
            call_user_func(array($root, 'set' . $this->titleField), $this->treeRootName);
            $this->getEntityManager()->persist($root);
            $this->getEntityManager()->flush();
        }
    }

    public function createQuery($context = 'list')
    {
        $queryBuilder = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from($this->getClass(), 'c')
            ->where('c.parent IS NOT NULL')
            ->addOrderBy('c.root', 'ASC')
            ->addOrderBy('c.left', 'ASC');

        $query = new ProxyQuery($queryBuilder);

        return $query;
    }

    public function toString($object)
    {
        return call_user_func(array($object, 'get' . $this->titleField));
    }
}
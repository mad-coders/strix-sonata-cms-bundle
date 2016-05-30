<?php

namespace Strix\SonataCmsBundle\Admin;

use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AbstractStrixSonataCmsAdmin extends Admin
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Enable sortable routes
     * @var bool
     */
    protected $isSortable = false;

    /**
     * Gedmo\Sortable field name
     * @var string
     */
    protected $sortableField = 'position';

    public function reverseBooleanStateAction()
    {
        $subject = $this->getSubject();
        $field = $this->request->get('field');
        $unique = $this->request->get('unique');

        $value = call_user_func(array($subject, 'get'.$field));

        call_user_func(array($subject, 'set'.$field), !$value);

        if (!$value && $unique == 1) {
            $this->getEntityManager()->createQueryBuilder()
                ->update($this->getClass(), 'c')
                ->set('c.'.$field, 0)
                ->getQuery()
                ->execute();
        }

        $this->getEntityManager()->flush();

        return new RedirectResponse($this->generateUrl('list'));
    }

    public function sortableUpAction()
    {
        $subject = $this->getSubject();

        $position = call_user_func(array($subject, 'get'.$this->sortableField));

        call_user_func(array($subject, 'set'.$this->sortableField), $position == 0 ? 0 : $position - 1);

        $this->getEntityManager()->flush();

        return new RedirectResponse($this->generateUrl('list'));
    }

    public function sortableDownAction()
    {
        $subject = $this->getSubject();

        $position = call_user_func(array($subject, 'get'.$this->sortableField));

        call_user_func(array($subject, 'set'.$this->sortableField), $position + 1);

        $this->getEntityManager()->flush();

        return new RedirectResponse($this->generateUrl('list'));
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        if ($container->isScopeActive('request')) {
            $this->request = $container->get('request');
        }
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);

        if ($this->isSortable) {
            $collection->add(
                $this->getNormalizedCode().'_sortable_up',
                $this->getRouterIdParameter() . '/sortable_up',
                array('_controller' => $this->code.':sortableUpAction')
            );
            $collection->add(
                $this->getNormalizedCode().'_sortable_down',
                $this->getRouterIdParameter() . '/sortable_down',
                array('_controller' => $this->code.':sortableDownAction')
            );
        }

        $collection->add(
            $this->getNormalizedCode().'_boolean_reverse',
            $this->getRouterIdParameter() . '/boolean_reverse/{field}/{unique}',
            array('_controller' => $this->code.':reverseBooleanStateAction')
        );
    }

    /**
     * Adds up/down fields for sortable
     *
     * @param ListMapper $list
     * @throws \Exception
     */
    protected function addSortableControls(ListMapper $list)
    {
        if (!$this->isSortable) {
            throw new \Exception('Please set "isSortable" to true to enable sortable behavior');
        }

        $list
            ->add('strix_sonata_cms_sortable_up_down', 'text', array('label' => 'Up / Down', 'template' => 'StrixSonataCmsBundle:Admin:list_sortable_up_down.html.twig'));
    }

    /**
     * Adds field for boolean that changes on click
     *
     * @param ListMapper $list
     * @param $field
     * @param bool $unique
     */
    protected function addBooleanControls(ListMapper $list, $field, $unique = false)
    {
        $list
            ->add($field, null, array('template' => 'StrixSonataCmsBundle:Admin:list_boolean_reverse.html.twig', 'unique' => (int)$unique));
    }

    /**
     * Gets normalized (letters and underscores only) code for this admin class, used for route names
     * @return string
     */
    public function getNormalizedCode()
    {
        return strtolower(preg_replace('/[^A-Za-z0-9_]/', '_', $this->code));
    }

    /**
     * @return string
     */
    public function getSortableField()
    {
        return $this->sortableField;
    }

    /**
     * @return string
     */
    public function getEnabledField()
    {
        return $this->enabledField;
    }

    /**
     * @return EntityRepository
     */
    protected function getRepository()
    {
        return $this->getEntityManager()->getRepository($this->getClass());
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        $em = $this->modelManager->getEntityManager($this->getClass());

        return $em;
    }

    /**
     * Gets active cms languages
     *
     * @param bool $fetchDisabled
     * @return array
     */
    public function getCmsLanguages($fetchDisabled = false)
    {
        $entityName = $this->getLanguageEntity();

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('l')
            ->from($entityName, 'l');

        if (!$fetchDisabled) {
            $qb->where('l.enabled = true');
        }

        return $qb->orderBy('l.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Getter for current language entity
     *
     * @return string
     * @throws \Exception
     */
    protected function getLanguageEntity()
    {
        $name = $this->container->getParameter('strix_sonata_cms.language_entity');

        if (!$name) {
            throw new \Exception('You need to set "language_entity" config option to use languages in admin');
        }

        return $name;
    }
}
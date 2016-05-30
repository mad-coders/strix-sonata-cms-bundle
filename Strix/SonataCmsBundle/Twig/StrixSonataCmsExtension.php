<?php

namespace Strix\SonataCmsBundle\Twig;

use Doctrine\ORM\Query;
use Gedmo\Translatable\TranslatableListener;

use Strix\SonataCmsBundle\Entity\AbstractLanguageEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StrixSonataCmsExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getLanguageList()
    {
        return $this->getEntityManager()
            ->getRepository($this->getLanguageEntityName())
            ->findBy(array('enabled' => true), array('position' => 'ASC'));
    }

    public function getRouteParams()
    {
        $result = array();
        foreach($this->getRequest()->attributes as $key => $val) {
            if(substr($key,0,1) == '_') {
                continue;
            } else {
                $result[$key] = $val;
            }
        }
        return $result;
    }

    public function getRouteForLanguage(AbstractLanguageEntity $language)
    {
        $category = $this->getCurrentCategory();

        $slug = $this->getSlugWalker()->getSlug($category, $language->getCode());

        $targetRoute = 'strix_sonata_cms_route_';
        $targetRoute .= strtolower(preg_replace('/[^A-Za-z0-9_]/', '_', $slug));
        $targetRoute .= '_' . $category->getId();
        $targetRoute .= '_' . $language->getCode();

        if ($language->getDefault()) {
            $targetRoute .= '_default';
        }

        if ($this->getRouter()->getRouteCollection()->get($targetRoute)) {
            return $targetRoute;
        }
        //let's fallback to default!
        $homeRoute = 'strix_sonata_cms_home_' . $language->getCode() .
            ($language->getDefault() ? '_default' : '');

        if (!$this->getRouter()->getRouteCollection()->get($homeRoute)) {
            throw new \Exception('Oh no! You have not a home route, you should really have one.');
        }

        return $homeRoute;
    }

    public function getHomeRoute()
    {
        $base = 'strix_sonata_cms_home_';

        $language = $this->getCurrentLanguageObject();

        $homeRoute = $base . $language->getCode() .
            ($language->getDefault() ? '_default' : '');

        if (!$this->getRouter()->getRouteCollection()->get($homeRoute)) {
            throw new \Exception('Oh no! You have not a home route, you should really have one.');
        }

        return $homeRoute;
    }

    public function getCategoriesByContext($contextName)
    {
        $root = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from($this->getTreeEntityName(), 'c')
            ->join('c.context', 'cx')
            ->where('cx.contextName = :contextName AND c.isEnabled = true')
            ->setParameter('contextName', $contextName)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        if (!$root) {
            return array();
        }

        return $this->getCategoriesByParent($root);
    }

    public function getCategoriesByTitle($internalTitle)
    {
        return $this->getCategoriesByParent($this->getCategoryByTitle($internalTitle));
    }

    public function getCategoryByTitle($internalTitle)
    {
        $node = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from($this->getTreeEntityName(), 'c')
            ->where('c.internalTitle = :title AND c.isEnabled = true')
            ->setParameter('title', $internalTitle)
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();

        return $node;
    }

    public function getCategoriesByParent($parent)
    {
        $this->container->get('stof_doctrine_extensions.listener.translatable')->setTranslatableLocale($this->getRequest()->getLocale());
        $nodes = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from($this->getTreeEntityName(), 'c')
            ->where('c.isEnabled = true AND c.parent = :parent')
            ->setParameter('parent', $parent)
            ->orderBy('c.left', 'ASC')
            ->getQuery()
            /*->setHint(
                Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
            )
            ->setHint(
                TranslatableListener::HINT_TRANSLATABLE_LOCALE,
                $this->getRequest()->getLocale()
            )
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 0)
            ->getResult(Query::HYDRATE_SIMPLEOBJECT);*/
            ->getResult();

        $result = array();

        foreach ($nodes as $node) {
            $slug = $this->getSlugWalker()->getSlug($node, $this->getRequest()->getLocale());

            if ($slug === false) {
                continue;
            }

            $result[] = $node;

            $node->setLocale($this->getRequest()->getLocale());
            $this->getEntityManager()->refresh($node);
        }

        return $result;
    }

    public function getCategoryRouteName($category)
    {
        $language = $this->getCurrentLanguageObject();

        //no checks since we assume that if this category is requested is it visible and slugs are ok
        if ($category->getIsHome()) {
            return 'strix_sonata_cms_home' . ($language->getDefault() ? '' : '_' . $language->getCode());
        }

        $slug = $this->getSlugWalker()->getSlug($category, $this->getRequest()->getLocale());

        $name = 'strix_sonata_cms_route_';

        $name .= strtolower(preg_replace('/[^A-Za-z0-9_]/', '_', $slug));

        $name .= '_' . $category->getId();

        $name .= '_' . $language->getCode();

        if ($language->getDefault()) {
            $name .= '_default';
        }

        return $name;
    }

    public function getCurrentCategory()
    {
        return $this->getEntityManager()
            ->getRepository($this->getTreeEntityName())
            ->findOneById($this->getRequest()->attributes->get('_cms_object_id'));
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('scms_language_list', array($this, 'getLanguageList')),
            new \Twig_SimpleFunction('scms_route_for_language', array($this, 'getRouteForLanguage')),
            new \Twig_SimpleFunction('scms_route_params', array($this, 'getRouteParams')),
            new \Twig_SimpleFunction('scms_home_route', array($this, 'getHomeRoute')),
            new \Twig_SimpleFunction('scms_categories_by_context', array($this, 'getCategoriesByContext')),
            new \Twig_SimpleFunction('scms_categories_by_parent', array($this, 'getCategoriesByParent')),
            new \Twig_SimpleFunction('scms_categories_by_title', array($this, 'getCategoriesByTitle')),
            new \Twig_SimpleFunction('scms_category_by_title', array($this, 'getCategoryByTitle')),
            new \Twig_SimpleFunction('scms_category_route_name', array($this, 'getCategoryRouteName')),
            new \Twig_SimpleFunction('scms_current_category', array($this, 'getCurrentCategory')),
        );
    }

    public function getName()
    {
        return 'strix_sonata_cms';
    }

    /**
     * @return AbstractLanguageEntity
     * @throws \Exception
     */
    protected function getCurrentLanguageObject()
    {
        $language = $this->getEntityManager()
            ->getRepository($this->getLanguageEntityName())
            ->findOneByCode($this->getRequest()->attributes->get('_locale'));

        if (!$language) {
            throw new \Exception('Can not detect current language. Looks like you messed something up very bad');
        }

        return $language;
    }

    /**
     * @return \Strix\SonataCmsBundle\Util\SlugWalker
     */
    protected function getSlugWalker()
    {
        return $this->container->get('strix.util.slug_walker');
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected function getRouter()
    {
        return $this->container->get('router');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getRequest()
    {
        return $this->container->get('request');
    }

    /**
     * @return string
     */
    protected function getLanguageEntityName()
    {
        return $this->container->getParameter('strix_sonata_cms.language_entity');
    }

    /**
     * @return string
     */
    protected function getTreeEntityName()
    {
        return $this->container->getParameter('strix_sonata_cms.tree_entity');
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }
}
<?php

namespace Strix\SonataCmsBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Loader implements LoaderInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $loaded = false;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * Loads a resource.
     *
     * @param mixed $resource The resource
     * @param string $type The resource type
     * @throws \RuntimeException
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function load($resource, $type = null)
    {
//        $this->container->get('routing.loader')->load('@StrixSonataCmsBundle/Resources/config/routing.yml');
        //$this->getResolver()->resolve('@StrixSonataCmsBundle/Resources/config/routing.yml')->load('@StrixSonataCmsBundle/Resources/config/routing.yml');

        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "strix_sonata_cms" loader twice');
        }


        $routes = new RouteCollection();

        $route = new Route('/strix/file/upload', array('_controller' => 'StrixSonataCmsBundle:Upload:file'));
        $routes->add('strix_helper_file_upload', $route);
        $route = new Route('/strix/image/upload', array('_controller' => 'StrixSonataCmsBundle:Upload:image'));
        $routes->add('strix_helper_image_upload', $route);


        if (!$this->container->getParameter('strix_sonata_cms.enable_router')) {
            return $routes;
        }

        if (!$this->container->getParameter('strix_sonata_cms.tree_entity')) {
            return $routes;
        }

        $locales = $this->container->get('doctrine')
            ->getManager()
            ->createQueryBuilder()
            ->select('l')
            ->from($this->container->getParameter('strix_sonata_cms.language_entity'), 'l')
            ->where('l.enabled = true')
            ->getQuery()
            ->getResult();

        $tree = $this->container->get('doctrine')
            ->getManager()
            ->createQueryBuilder()
            ->select('c')
            ->from($this->container->getParameter('strix_sonata_cms.tree_entity'), 'c')
            ->where('c.isEnabled = true')
            ->getQuery()
            ->getResult();

        foreach ($tree as $item) {
            foreach ($locales as $locale) {

                $slug = $this->container->get('strix.util.slug_walker')->getSlug($item, $locale->getCode());

                if ($slug === false) {
                    continue;
                }

                $slugPart = strtolower(preg_replace('/[^A-Za-z0-9_]/', '_', $slug));

                if ($item->getIsHome()) {
                    $baseRouteName = 'strix_sonata_cms_home_';
                    $name = 'strix_sonata_cms_home_' . $locale->getCode();
                } else {
                    $baseRouteName = sprintf('strix_sonata_cms_route_%s_%d_', $slugPart, $item->getId());
                    $name = sprintf('strix_sonata_cms_route_%s_%d_%s', $slugPart, $item->getId(), $locale->getCode());
                }

                $defaultsItem = $item;

                if ($item->getUseDirectChild() === true && count($item->getChildren()) > 0) {
                    $item = $item->getChildren()[0];
                }

                $defaults = array(
                    '_cms_object_id' => $item->getId(),
                    '_controller' => $this->findController($item),
                    '_cms_template' => $this->findTemplate($item),
                    '_locale' => $locale->getCode(),
                    '_base_route_name' => $baseRouteName
                );

                if ($params = $item->getRouteParams()) {
                    parse_str($params, $params);

                    foreach ($params as $key => $val) {
                        if ($key) {
                            if ($slug[strlen($slug) - 1] != '/') {
                                $slug = $slug . '/';
                            }

                            $slug .= '{' . $key . '}';

                            $defaults[$key] = $val;
                        }
                    }
                }

                $localizedSlug = '/' . $locale->getCode() . '/' . $slug;

                $pattern = $localizedSlug;

                $routes->add($name, new Route(rtrim($pattern, '/'), $defaults));

                //default route should have a shorter patter w/o locale
                if ($locale->getDefault()) {
                    $routes->add($name . '_default', new Route(rtrim($slug, '/'), $defaults));
                }
            }
        }

        $this->loaded = true;

        return $routes;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed $resource A resource
     * @param string $type The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return 'strix_sonata_cms' == $type;
    }

    /**
     * Gets the loader resolver.
     *
     * @return LoaderResolverInterface A LoaderResolverInterface instance
     */
    public function getResolver()
    {
        // TODO: Implement getResolver() method.
    }

    /**
     * Sets the loader resolver.
     *
     * @param LoaderResolverInterface $resolver A LoaderResolverInterface instance
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
        // TODO: Implement setResolver() method.
    }

    protected function findController($item)
    {
        $controller = $this->container->getParameter('strix_sonata_cms.default_controller');

        while(!$item->getController() && $item->getParent()) {
            $item = $item->getParent();
        }

        if ($item->getController()) {
            $controller = $item->getController()->getControllerName();
        }

        return $controller;
    }

    protected function findTemplate($item)
    {
        $template = false;

        while(!$item->getTemplate() && $item->getParent()) {
            $item = $item->getParent();
        }

        if ($item->getTemplate()) {
            $template = $item->getTemplate()->getTemplateName();
        }

        return $template;
    }
}

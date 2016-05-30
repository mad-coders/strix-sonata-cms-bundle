<?php

namespace Strix\SonataCmsBundle\Util;

use Doctrine\ORM\EntityManager;

class SlugWalker
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Yeah, this is ugly
     *
     * @var array
     */
    protected $slugCache;

    /**
     * This is even uglier
     *
     * @var array
     */
    protected $translationCache;

    /**
     * Tree entity name
     *
     * @var
     */
    protected $entityName;

    /**
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param mixed $entityName
     * @return $this
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function setEntityManager($em)
    {
        $this->em = $em;
    }

    public function getSlug($object, $locale)
    {
        $key = spl_object_hash($object) . '_' . $locale;

        if (isset($this->slugCache[$key])) {
            return $this->slugCache[$key];
        }

        if ($object->getIsHome()) {
            $this->slugCache[$key] = '';
            return '';
        }

        $slug = $this->getObjectSlug($object, $locale);

        if (!$slug) {
            $this->slugCache[$key] = false;
            return false;
        }

        $parent = $object;

        while ($parent = $parent->getParent()) {
            if ($parent->getIsHome()) {
                break;
            }

            $parentSlug = $this->getObjectSlug($parent, $locale);

            if ($parentSlug == '') {
                $this->slugCache[$key] = false;
                return false;
            }

            $slug = $parentSlug . '/' . $slug;
        }

        $this->slugCache[$key] = $slug;

        return $slug;
    }

    protected function getObjectSlug($object, $locale)
    {
        if ($this->translationCache == []) {
            $this->translationCache = $this->em
                ->getRepository($this->getEntityName() . 'Translation')
                ->findAll();
        }

        foreach ($this->translationCache as $translation) {
            if ($translation->getField() == 'slug' &&
                $translation->getObject() == $object &&
                $translation->getLocale() == $locale
            ) {
                return $translation->getContent();
            }
        }

        /*$slug = $this->em->createQueryBuilder()
            ->select('t')
            ->from($this->getEntityName().'Translation', 't')
            ->where('t.field = :field AND t.object = :object AND t.locale = :locale')
            ->setParameters(array(
                'field' => 'slug',
                'object' => $object,
                'locale' => $locale
            ))
            ->getQuery()
            ->getOneOrNullResult();

        if ($slug) {
            return $slug->getContent();
        }*/

        return false;
    }

}
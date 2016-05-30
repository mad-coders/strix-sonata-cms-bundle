<?php

namespace Strix\SonataCmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

abstract class AbstractCmsContextEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=128, name="context_name")
     * @var string
     */
    protected $contextName;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $contextName
     * @return $this
     */
    public function setContextName($contextName)
    {
        $this->contextName = $contextName;

        return $this;
    }

    /**
     * @return string
     */
    public function getContextName()
    {
        return $this->contextName;
    }
}
<?php

namespace Strix\SonataCmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

abstract class AbstractControllerEntity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=128, name="controller_name")
     * @var string
     */
    protected $controllerName;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $controllerName
     * @return $this
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;

        return $this;
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }
}
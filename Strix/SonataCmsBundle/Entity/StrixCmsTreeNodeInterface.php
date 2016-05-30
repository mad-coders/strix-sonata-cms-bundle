<?php

namespace Strix\SonataCmsBundle\Entity;

interface StrixCmsTreeNodeInterface
{
    /**
     * Must return a string with field name containing title attribute,
     * it will be used to figure out getters / setters of a title
     */
    public function getTitleFieldName();
}
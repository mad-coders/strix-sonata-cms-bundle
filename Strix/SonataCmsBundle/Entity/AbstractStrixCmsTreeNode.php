<?php

namespace Strix\SonataCmsBundle\Entity;

abstract class AbstractStrixCmsTreeNode implements StrixCmsTreeNodeInterface
{
    /**
     * Returns title padded from left according to tree level
     *
     * @return string
     */
    public function getTreeLevelTitle()
    {
        return sprintf('%s%s',
            $this->getLevel() <= 1 ? '' : str_repeat('&nbsp;&nbsp;-&nbsp;&nbsp;', ($this->getLevel() - 1) * 1),
            call_user_func(array($this, 'get' . $this->getTitleFieldName()))
        );
    }

    /**
     * Returns title for use in select
     *
     * @return string
     */
    public function getTreeSelectLevelTitle()
    {
        return sprintf('%s%s',
            $this->getLevel() <= 1 ? '' : str_repeat('  -  ', ($this->getLevel() - 1) * 1),
            call_user_func(array($this, 'get' . $this->getTitleFieldName()))
        );
    }
}
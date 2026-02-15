<?php

namespace Aropixel\AdminBundle\Component\Menu\Builder;

use Aropixel\AdminBundle\Component\Menu\Model\Menu;

interface AdminMenuBuilderInterface
{
    /**
     * @return Menu[]
     */
    public function buildMenu(): array;
}

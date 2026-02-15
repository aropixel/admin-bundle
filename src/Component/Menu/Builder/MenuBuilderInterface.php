<?php

namespace Aropixel\AdminBundle\Component\Menu\Builder;

use Aropixel\AdminBundle\Component\Menu\Model\Menu;

interface MenuBuilderInterface
{
    public function buildMenu(string $id = 'menu'): Menu;
}

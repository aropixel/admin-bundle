<?php

namespace Aropixel\AdminBundle\Domain\Menu\Builder;

use Aropixel\AdminBundle\Domain\Menu\Model\Menu;

interface MenuBuilderInterface
{
    public function buildMenu(): Menu;
}

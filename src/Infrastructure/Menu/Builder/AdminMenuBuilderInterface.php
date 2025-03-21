<?php

namespace Aropixel\AdminBundle\Infrastructure\Menu\Builder;

use Aropixel\AdminBundle\Domain\Menu\Model\Menu;

interface AdminMenuBuilderInterface
{
    /**
     * @return Menu[]
     */
    public function buildMenu(): array;
}

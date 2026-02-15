<?php

namespace Aropixel\AdminBundle\Component\Menu\Builder;

use Aropixel\AdminBundle\Component\Menu\Model\ItemInterface;

interface QuickMenuBuilderInterface
{
    /**
     * @return array<int,ItemInterface>
     */
    public function buildMenu(): array;
}

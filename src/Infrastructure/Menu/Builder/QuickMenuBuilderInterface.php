<?php

namespace Aropixel\AdminBundle\Infrastructure\Menu\Builder;

use Aropixel\AdminBundle\Domain\Menu\Model\ItemInterface;

interface QuickMenuBuilderInterface
{
    /**
     * @return array<int,ItemInterface>
     */
    public function buildMenu(): array;
}

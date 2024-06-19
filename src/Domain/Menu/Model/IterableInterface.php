<?php

namespace Aropixel\AdminBundle\Domain\Menu\Model;

use Aropixel\AdminBundle\Domain\Menu\Model\ItemInterface;

interface IterableInterface
{
    /**
     * @return ItemInterface[]
     */
    public function getItems(): array;
}

<?php

namespace Aropixel\AdminBundle\Domain\Menu\Model;

interface IterableInterface
{
    /**
     * @return ItemInterface[]
     */
    public function getItems(): array;
}

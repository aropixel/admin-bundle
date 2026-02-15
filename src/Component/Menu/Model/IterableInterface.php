<?php

namespace Aropixel\AdminBundle\Component\Menu\Model;

interface IterableInterface
{
    /**
     * @return ItemInterface[]
     */
    public function getItems(): array;
}

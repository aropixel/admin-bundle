<?php

namespace Aropixel\AdminBundle\Component\Menu\Model;

interface RoutableInterface
{
    public function getRouteName(): string;

    /**
     * @return array<mixed>
     */
    public function getRouteParameters(): array;
}

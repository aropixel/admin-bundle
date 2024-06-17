<?php

namespace Aropixel\AdminBundle\Domain\Menu\Model;

interface RoutableInterface
{
    public function getRouteName(): string;

    public function getRouteParameters(): array;
}

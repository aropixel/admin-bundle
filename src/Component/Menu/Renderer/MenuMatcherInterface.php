<?php

namespace Aropixel\AdminBundle\Component\Menu\Renderer;

use Aropixel\AdminBundle\Component\Menu\Model\Menu;

interface MenuMatcherInterface
{
    /**
     * @param array<string> $forceMatchRouteParams
     */
    public function mustMatch(?string $forceMatchRoute = null, array $forceMatchRouteParams = []): void;

    public function matchActive(Menu $menu): void;
}

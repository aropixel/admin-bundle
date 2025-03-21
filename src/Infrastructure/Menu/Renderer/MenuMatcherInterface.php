<?php

namespace Aropixel\AdminBundle\Infrastructure\Menu\Renderer;

use Aropixel\AdminBundle\Domain\Menu\Model\Menu;

interface MenuMatcherInterface
{
    /**
     * @param array<string> $forceMatchRouteParams
     */
    public function mustMatch(?string $forceMatchRoute = null, array $forceMatchRouteParams = []): void;

    public function matchActive(Menu $menu): void;
}

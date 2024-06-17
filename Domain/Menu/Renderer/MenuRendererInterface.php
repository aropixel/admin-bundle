<?php

namespace Aropixel\AdminBundle\Domain\Menu\Renderer;

use Aropixel\AdminBundle\Domain\Menu\Model\Menu;

interface MenuRendererInterface
{
    public function renderMenu(Menu $menu): string;
}

<?php

namespace Aropixel\AdminBundle\Domain\Menu\Renderer;

use Aropixel\AdminBundle\Domain\Menu\Model\Menu;

interface MenuRendererInterface
{
    public function renderMenu(Menu $menu, string $template = '@AropixelAdmin/Menu/menu.html.twig', array $params = []): string;

    public function renderSearchMenu(array $menus, string $template): string;

    public function renderFullMenu(array $menus, string $template): string;
}

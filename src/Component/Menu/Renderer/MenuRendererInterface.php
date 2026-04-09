<?php

namespace Aropixel\AdminBundle\Component\Menu\Renderer;

use Aropixel\AdminBundle\Component\Menu\Model\Menu;

interface MenuRendererInterface
{
    /**
     * @param array<mixed> $params
     */
    public function renderMenu(Menu $menu, string $template = '@AropixelAdmin/Menu/menu.html.twig', array $params = []): string;

    /**
     * @param Menu[] $menus
     */
    public function renderSearchMenu(array $menus, string $template): string;

    /**
     * @param Menu[] $menus
     */
    public function renderFullMenu(array $menus, string $template): string;
}

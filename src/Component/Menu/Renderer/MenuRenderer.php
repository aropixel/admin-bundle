<?php

namespace Aropixel\AdminBundle\Component\Menu\Renderer;

use Aropixel\AdminBundle\Component\Menu\Model\Menu;
use Twig\Environment;

class MenuRenderer implements MenuRendererInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly MenuMatcherInterface $menuMatcher
    ) {
    }

    public function renderMenu(Menu $menu, string $template = '@AropixelAdmin/Menu/menu.html.twig', array $params = []): string
    {
        $this->menuMatcher->matchActive($menu);
        $params['menu'] = $menu;

        return $this->twig->render($template, $params);
    }

    public function renderSearchMenu(array $menus, string $template): string
    {
        return $this->twig->render($template, ['menus' => $menus]);
    }

    public function renderFullMenu(array $menus, string $template): string
    {
        return $this->twig->render($template, ['menus' => $menus]);
    }
}

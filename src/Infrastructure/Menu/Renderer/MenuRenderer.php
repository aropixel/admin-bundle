<?php

namespace Aropixel\AdminBundle\Infrastructure\Menu\Renderer;

use Aropixel\AdminBundle\Domain\Menu\Model\Menu;
use Aropixel\AdminBundle\Domain\Menu\Renderer\MenuRendererInterface;
use Aropixel\AdminBundle\Infrastructure\Menu\Renderer\MenuMatcherInterface;
use Twig\Environment;

class MenuRenderer implements MenuRendererInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly MenuMatcherInterface $menuMatcher
    ) {
    }

    public function renderMenu(Menu $menu, string $template = '@AropixelAdmin/Menu/menu.html.twig', $params = [], ?string $forceMatchRoute = null): string
    {
        $this->menuMatcher->matchActive($menu, $forceMatchRoute);
        $params['menu'] = $menu;

        return $this->twig->render($template, $params);
    }
}

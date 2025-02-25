<?php

namespace Aropixel\AdminBundle\Infrastructure\Menu\Renderer;

use Aropixel\AdminBundle\Domain\Menu\Model\Menu;
use Aropixel\AdminBundle\Domain\Menu\Renderer\MenuRendererInterface;
use Twig\Environment;

class MenuRenderer implements MenuRendererInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly MenuMatcherInterface $menuMatcher
    ) {
    }

    /**
     * @param array<mixed> $params
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderMenu(Menu $menu, string $template = '@AropixelAdmin/Menu/menu.html.twig', array $params = []): string
    {
        $this->menuMatcher->matchActive($menu);
        $params['menu'] = $menu;

        return $this->twig->render($template, $params);
    }

    /**
     * @param array<mixed> $menus
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderSearchMenu(array $menus, string $template): string
    {
        return $this->twig->render($template, ['menus' => $menus]);
    }

    /**
     * @param array<mixed> $menus
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderFullMenu(array $menus, string $template): string
    {
        return $this->twig->render($template, ['menus' => $menus]);
    }
}

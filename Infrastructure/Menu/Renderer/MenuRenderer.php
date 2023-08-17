<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 09/02/2023 à 12:06
 */

namespace Aropixel\AdminBundle\Infrastructure\Menu\Renderer;

use Aropixel\AdminBundle\Domain\Menu\Model\Menu;
use Aropixel\AdminBundle\Domain\Menu\Renderer\MenuRendererInterface;
use Twig\Environment;

class MenuRenderer implements MenuRendererInterface
{
    private Environment $twig;
    private MenuMatcher $menuMatcher;

    /**
     * @param Environment $twig
     * @param MenuMatcher $menuMatcher
     */
    public function __construct(Environment $twig, MenuMatcher $menuMatcher)
    {
        $this->twig = $twig;
        $this->menuMatcher = $menuMatcher;
    }


    public function renderMenu(Menu $menu, string $template = "@AropixelAdmin/Menu/menu.html.twig", $params=[]) : string
    {
        $this->menuMatcher->matchActive($menu);
        $params['menu'] = $menu;
        return $this->twig->render($template, $params);
    }
}

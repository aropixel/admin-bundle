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


    public function renderMenu(Menu $menu) : string
    {
        $this->menuMatcher->matchActive($menu);
        return $this->twig->render('@AropixelAdmin/Menu/menu.html.twig', ['menu' => $menu]);
    }
}

<?php
namespace Aropixel\AdminBundle\Infrastructure\Menu\Twig;

use Aropixel\AdminBundle\Domain\Menu\Builder\MenuBuilderInterface;
use Aropixel\AdminBundle\Domain\Menu\Renderer\MenuRendererInterface;
use Aropixel\AdminBundle\Infrastructure\Menu\Renderer\MenuMatcherInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class MenuExtension extends AbstractExtension
{
    private MenuBuilderInterface $menuBuilder;
    private MenuMatcherInterface $menuMatcher;
    private MenuRendererInterface $menuRenderer;

    /**
     * @param MenuBuilderInterface $menuBuilder
     * @param MenuMatcherInterface $menuMatcher
     * @param MenuRendererInterface $menuRenderer
     */
    public function __construct(MenuBuilderInterface $menuBuilder, MenuMatcherInterface $menuMatcher, MenuRendererInterface $menuRenderer)
    {
        $this->menuBuilder = $menuBuilder;
        $this->menuMatcher = $menuMatcher;
        $this->menuRenderer = $menuRenderer;
    }


    public function getFunctions()
    {
        return [
            new TwigFunction('aropixel_admin_render_menu', array($this, 'renderMenus'), array(
                'is_safe' => array('html'),
                'needs_environment' => true
            )),
            new TwigFunction('set_aropixel_menu_match_route', array($this, 'matchRoute'))
        ];
    }


    public function matchRoute(string $matchRoute, array $matchRouteParams = [])
    {
        $this->menuMatcher->mustMatch($matchRoute, $matchRouteParams);
    }


    public function renderMenus()
    {
        $menu = $this->menuBuilder->buildMenu();
        return $this->menuRenderer->renderMenu($menu);
    }

}

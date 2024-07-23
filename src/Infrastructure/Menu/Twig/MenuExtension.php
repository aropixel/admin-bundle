<?php
namespace Aropixel\AdminBundle\Infrastructure\Menu\Twig;

use Aropixel\AdminBundle\Domain\Menu\Builder\MenuBuilderInterface;
use Aropixel\AdminBundle\Domain\Menu\Model\Menu;
use Aropixel\AdminBundle\Domain\Menu\Renderer\MenuRendererInterface;
use Aropixel\AdminBundle\Infrastructure\Menu\Builder\AdminMenuBuilderInterface;
use Aropixel\AdminBundle\Infrastructure\Menu\Builder\QuickMenuBuilderInterface;
use Aropixel\AdminBundle\Infrastructure\Menu\Renderer\MenuMatcherInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class MenuExtension extends AbstractExtension
{

    public array $menu = [];

    public function __construct(
        private readonly MenuBuilderInterface $menuBuilder,
        private readonly MenuMatcherInterface $menuMatcher,
        private readonly MenuRendererInterface $menuRenderer,
        private readonly AdminMenuBuilderInterface $adminMenuBuilder,
        private readonly QuickMenuBuilderInterface $quickMenuBuilder,
    ){
    }


    public function getFunctions()
    {
        return [
            new TwigFunction('aropixel_admin_render_menu', [$this, 'renderMenus'], [
                'is_safe' => ['html'],
                'needs_environment' => true
            ]),
            new TwigFunction('set_aropixel_menu_match_route', [$this, 'matchRoute']),
            new TwigFunction('get_quick_menu', $this->getQuickMenu(...)),
            new TwigFunction('render_admin_menu', $this->renderMenu(...), ['is_safe' => ['html']]),
            new TwigFunction('render_search_menu', $this->renderSearchMenu(...), ['is_safe' => ['html']]),
            new TwigFunction('render_menu', $this->renderAllMenus(...), ['is_safe' => ['html']]),
        ];
    }


    public function matchRoute(string $matchRoute, array $matchRouteParams = [])
    {
        $this->menuMatcher->mustMatch($matchRoute, $matchRouteParams);
    }

    public function renderMenu(Menu $menu): string
    {
        return $this->menuRenderer->renderMenu($menu, '@AropixelAdmin/Menu/menu.html.twig');
    }

    public function renderMenus()
    {
        $menu = $this->menuBuilder->buildMenu('admin');
        return $this->menuRenderer->renderMenu($menu);
    }

    public function getMenu(): array
    {
        if (!empty($this->menu)) {
            return $this->menu;
        }

        $this->menu = $this->adminMenuBuilder->buildMenu();

        return $this->menu;
    }

    public function renderAllMenus(): string
    {
        $menus = $this->getMenu();

        return $this->menuRenderer->renderFullMenu($menus, '@AropixelAdmin/Menu/_fullscreen-nav.html.twig');
    }

    public function renderSearchMenu(): string
    {
        $menus = $this->getMenu();

        return $this->menuRenderer->renderSearchMenu($menus, '@AropixelAdmin/Menu/_search-nav-result.html.twig');
    }

    public function getQuickMenu()
    {
        return $this->quickMenuBuilder->buildMenu();
    }

}

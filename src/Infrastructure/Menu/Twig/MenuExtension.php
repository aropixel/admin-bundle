<?php

namespace Aropixel\AdminBundle\Infrastructure\Menu\Twig;

use Aropixel\AdminBundle\Domain\Menu\Builder\MenuBuilderInterface;
use Aropixel\AdminBundle\Domain\Menu\Renderer\MenuRendererInterface;
use Aropixel\AdminBundle\Infrastructure\Menu\Renderer\MenuMatcherInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MenuExtension extends AbstractExtension
{
    public function __construct(
        private readonly MenuBuilderInterface $menuBuilder,
        private readonly MenuMatcherInterface $menuMatcher,
        private readonly MenuRendererInterface $menuRenderer
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('aropixel_admin_render_menu', $this->renderMenus(...), ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('set_aropixel_menu_match_route', $this->matchRoute(...)),
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

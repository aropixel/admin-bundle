<?php
namespace Aropixel\AdminBundle\Infrastructure\Menu\Twig;

use Aropixel\AdminBundle\Domain\Menu\Builder\MenuBuilderInterface;
use Aropixel\AdminBundle\Domain\Menu\Renderer\MenuRendererInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class MenuExtension extends AbstractExtension
{
    private MenuBuilderInterface $menuBuilder;
    private MenuRendererInterface $menuRenderer;


    /**
     * @param MenuBuilderInterface $menuBuilder
     * @param MenuRendererInterface $menuRenderer
     */
    public function __construct(MenuBuilderInterface $menuBuilder, MenuRendererInterface $menuRenderer)
    {
        $this->menuBuilder = $menuBuilder;
        $this->menuRenderer = $menuRenderer;
    }

    public function getFunctions()
    {
        return array(
            'app_menu' => new TwigFunction('aropixel_admin_render_menu', array($this, 'renderMenus'), array(
                'is_safe' => array('html'),
                'needs_environment' => true
            ))
        );
    }


    public function renderMenus()
    {
        $menu = $this->menuBuilder->buildMenu();
        return $this->menuRenderer->renderMenu($menu);
    }

}

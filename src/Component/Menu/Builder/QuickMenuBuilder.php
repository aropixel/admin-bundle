<?php

namespace Aropixel\AdminBundle\Component\Menu\Builder;

use Aropixel\AdminBundle\Component\Menu\Model\Link;
use Symfony\Component\Routing\RouterInterface;

class QuickMenuBuilder implements QuickMenuBuilderInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly array $menusConfig
    ) {
    }

    public function buildMenu(): array
    {
        $quickMenu = [];

        if ($this->routeExists('aropixel_page_index')) {
            $quickMenu[1] = new Link('menu.page.list', 'aropixel_page_index', ['type' => 'default']);
        }

        if ($this->routeExists('aropixel_blog_post_index')) {
            $quickMenu[2] = new Link('menu.blog.list', 'aropixel_blog_post_index', []);
        }

        if ($this->routeExists('aropixel_contact_index')) {
            $quickMenu[3] = new Link('menu.contact.label', 'aropixel_contact_index', []);
        }

        if ($this->routeExists('aropixel_menu_index')) {
            foreach ($this->menusConfig as $menuType => $config) {
                $label = $config['name'] ?? 'menu.menu.' . $menuType;
                $quickMenu[] = new Link($label, 'aropixel_menu_index', ['type' => $menuType]);
            }
        }

        if ($this->routeExists('aropixel_admin_user_index')) {
            $quickMenu[5] = new Link('menu.user.list', 'aropixel_admin_user_index', []);
        }

        return $quickMenu;
    }

    private function routeExists(string $name): bool
    {
        return !(null === $this->router->getRouteCollection()->get($name));
    }
}

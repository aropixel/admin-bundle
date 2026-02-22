<?php

namespace Aropixel\AdminBundle\Component\Menu\Builder;

use Aropixel\AdminBundle\Component\Menu\Model\Link;
use Symfony\Component\Routing\RouterInterface;

class QuickMenuBuilder implements QuickMenuBuilderInterface
{
    public function __construct(
        private readonly RouterInterface $router,
    ) {
    }

    public function buildMenu(): array
    {
        $quickMenu = [];

        if ($this->routeExists('aropixel_page_index')) {
            $quickMenu[1] = new Link('menu.page.list', 'aropixel_page_index', ['type' => 'default'], ['icon' => 'fas fa-pen']);
        }

        if ($this->routeExists('aropixel_blog_post_index')) {
            $quickMenu[2] = new Link('menu.blog.list', 'aropixel_blog_post_index', [], ['icon' => 'fas fa-newspaper']);
        }

        if ($this->routeExists('aropixel_contact_index')) {
            $quickMenu[3] = new Link('menu.contact.label', 'aropixel_contact_index', [], ['icon' => 'far fa-envelope']);
        }

        if ($this->routeExists('aropixel_menu_index')) {
            $quickMenu[4] = new Link('menu.menu.navbar', 'aropixel_menu_index', ['type' => 'navbar'], ['icon' => 'fas fa-bars']);
        }

        if ($this->routeExists('aropixel_admin_user_index')) {
            $quickMenu[5] = new Link('menu.user.list', 'aropixel_admin_user_index', [], ['icon' => 'fas fa-user-cog']);
        }

        return $quickMenu;
    }

    private function routeExists(string $name): bool
    {
        return !(null === $this->router->getRouteCollection()->get($name));
    }
}

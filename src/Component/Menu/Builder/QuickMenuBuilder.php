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
            $quickMenu[1] = new Link('Pages', 'aropixel_page_index', ['type' => 'default'], ['icon' => 'fas fa-pen']);
        }

        if ($this->routeExists('aropixel_blog_post_index')) {
            $quickMenu[2] = new Link('Actualités', 'aropixel_blog_post_index', [], ['icon' => 'fas fa-newspaper']);
        }

        if ($this->routeExists('aropixel_contact_index')) {
            $quickMenu[3] = new Link('Messagerie', 'aropixel_contact_index', [], ['icon' => 'far fa-envelope']);
        }

        if ($this->routeExists('aropixel_menu_index')) {
            $quickMenu[4] = new Link('Menu', 'aropixel_menu_index', ['type' => 'navbar'], ['icon' => 'fas fa-bars']);
        }

        if ($this->routeExists('aropixel_admin_user_index')) {
            $quickMenu[5] = new Link('Administrateurs', 'aropixel_admin_user_index', [], ['icon' => 'fas fa-user-cog']);
        }

        return $quickMenu;
    }

    private function routeExists(string $name): bool
    {
        return !(null === $this->router->getRouteCollection()->get($name));
    }
}

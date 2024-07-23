<?php

namespace Aropixel\AdminBundle\Infrastructure\Menu\Builder;

use Aropixel\AdminBundle\Domain\Menu\Model\Link;
use Aropixel\AdminBundle\Domain\Menu\Model\Menu;
use Symfony\Component\Routing\RouterInterface;

class AdminMenuBuilder implements AdminMenuBuilderInterface
{
    public const MENU_USER_ID = 'users';
    public const MENU_USER_LABEL = 'Utilisateurs';
    public const MENU_BLOG_ID = 'blog';
    public const MENU_BLOG_LABEL = 'Actualités';
    public const MENU_PAGE_ID = 'page';
    public const MENU_PAGE_LABEL = 'Pages';
    public const MENU_CONTACT_ID = 'contact';
    public const MENU_CONTACT_LABEL = 'Messagerie';
    public const MENU_MENU_ID = 'menu';
    public const MENU_MENU_LABEL = 'Menus';


    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function buildMenu(): array
    {
        $menu = [];

        if ($this->routeExists('aropixel_blog_post_index')) {
            $menu[] = $this->buildBlogMenu();
        }

        if ($this->routeExists('aropixel_page_index')) {
            $menu[] = $this->buildPageMenu();
        }

        if ($this->routeExists('aropixel_contact_index')) {
            $menu[] = $this->buildContactMenu();
        }

        if ($this->routeExists('aropixel_menu_index')) {
            $menu[] = $this->buildMenuMenu();
        }

        $menu[] = $this->buildUserMenu();

        return $menu;
    }

    public function buildUserMenu(): Menu
    {
        $menu = new Menu(self::MENU_USER_ID, self::MENU_USER_LABEL);
        $menu->addItem(new Link('Administrateurs', 'aropixel_admin_user_index', [], ['icon' => 'fas fa-user-cog']));
        $menu->addItem(new Link('Créer un administrateur', 'aropixel_admin_user_new', [], ['icon' => 'fas fa-user-cog']));

        return $menu;
    }

    public function buildBlogMenu(): Menu
    {
        $menu = new Menu(self::MENU_BLOG_ID, self::MENU_BLOG_LABEL);
        $menu->addItem(new Link('Actualités', 'aropixel_blog_post_index', [], ['icon' => 'fas fa-newspaper']));
        $menu->addItem(new Link('Créer une actualité', 'aropixel_blog_post_new', [], ['icon' => 'fas fa-newspaper']));

        return $menu;
    }

    public function buildPageMenu(): Menu
    {
        $menu = new Menu(self::MENU_PAGE_ID, self::MENU_PAGE_LABEL);
        $menu->addItem(new Link('Pages', 'aropixel_page_index', ['type' => 'default'], ['icon' => 'fas fa-pen']));
        $menu->addItem(new Link('Créer une page', 'aropixel_page_new', ['type' => 'default'], ['icon' => 'fas fa-pen']));

        return $menu;
    }

    public function buildMenuMenu(): Menu
    {
        $menu = new Menu(self::MENU_MENU_ID, self::MENU_MENU_LABEL);
        $menu->addItem(new Link('Menu', 'aropixel_menu_index', ['type' => 'navbar'], ['icon' => 'fas fa-bars']));
        $menu->addItem(new Link('Footer', 'aropixel_menu_index', ['type' => 'footer'], ['icon' => 'fas fa-bars']));

        return $menu;
    }

    public function buildContactMenu(): Menu
    {
        $menu = new Menu(self::MENU_CONTACT_ID, self::MENU_CONTACT_LABEL);
        $menu->addItem(new Link('Messagerie', 'aropixel_contact_index', [], ['icon' => 'far fa-envelope']));

        return $menu;
    }


    public function routeExists($name): bool
    {
        return !(null === $this->router->getRouteCollection()->get($name));
    }
}

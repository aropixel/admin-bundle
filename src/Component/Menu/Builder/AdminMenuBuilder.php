<?php

namespace Aropixel\AdminBundle\Component\Menu\Builder;

use Aropixel\AdminBundle\Component\Menu\Model\Link;
use Aropixel\AdminBundle\Component\Menu\Model\Menu;
use Symfony\Component\Routing\RouterInterface;

class AdminMenuBuilder implements AdminMenuBuilderInterface
{
    public const MENU_USER_ID = 'users';
    public const MENU_USER_LABEL = 'menu.user.label';
    public const MENU_BLOG_ID = 'blog';
    public const MENU_BLOG_LABEL = 'menu.blog.label';
    public const MENU_PAGE_ID = 'page';
    public const MENU_PAGE_LABEL = 'menu.page.label';
    public const MENU_CONTACT_ID = 'contact';
    public const MENU_CONTACT_LABEL = 'menu.contact.label';
    public const MENU_MENU_ID = 'menu';
    public const MENU_MENU_LABEL = 'menu.menu.label';

    public function __construct(
        private readonly RouterInterface $router,
        private readonly array $menusConfig
    ) {
    }

    /**
     * @return Menu[]
     */
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
        $menu->addItem(new Link('menu.user.list', 'aropixel_admin_user_index', [], ['icon' => 'fas fa-user-cog']));
        $menu->addItem(new Link('menu.user.new', 'aropixel_admin_user_new', [], ['icon' => 'fas fa-user-cog']));

        return $menu;
    }

    public function buildBlogMenu(): Menu
    {
        $menu = new Menu(self::MENU_BLOG_ID, self::MENU_BLOG_LABEL);
        $menu->addItem(new Link('menu.blog.list', 'aropixel_blog_post_index', [], ['icon' => 'fas fa-newspaper']));
        $menu->addItem(new Link('menu.blog.new', 'aropixel_blog_post_new', [], ['icon' => 'fas fa-newspaper']));

        return $menu;
    }

    public function buildPageMenu(): Menu
    {
        $menu = new Menu(self::MENU_PAGE_ID, self::MENU_PAGE_LABEL);
        $menu->addItem(new Link('menu.page.list', 'aropixel_page_index', ['type' => 'default'], ['icon' => 'fas fa-pen']));
        $menu->addItem(new Link('menu.page.new', 'aropixel_page_new', ['type' => 'default'], ['icon' => 'fas fa-pen']));

        return $menu;
    }

    public function buildMenuMenu(): Menu
    {
        $menu = new Menu(self::MENU_MENU_ID, self::MENU_MENU_LABEL);

        foreach ($this->menusConfig as $menuType => $config) {
            $label = $config['name'] ?? 'menu.menu.'.$menuType;
            $menu->addItem(new Link($label, 'aropixel_menu_index', ['type' => $menuType], ['icon' => 'fas fa-bars']));
        }

        return $menu;
    }

    public function buildContactMenu(): Menu
    {
        $menu = new Menu(self::MENU_CONTACT_ID, self::MENU_CONTACT_LABEL);
        $menu->addItem(new Link('menu.contact.label', 'aropixel_contact_index', [], ['icon' => 'far fa-envelope']));

        return $menu;
    }

    public function routeExists(string $name): bool
    {
        return !(null === $this->router->getRouteCollection()->get($name));
    }
}

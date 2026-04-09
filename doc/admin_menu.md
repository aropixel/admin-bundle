# Admin Menu Customization

By default, the admin menu is built by the `AdminMenuBuilder` class. However, you can easily customize it by creating your own menu builder that implements the `AdminMenuBuilderInterface`.

## Custom Menu Builder

To create a custom menu builder, you need to create a class that implements `Aropixel\AdminBundle\Component\Menu\Builder\AdminMenuBuilderInterface`. You should also use Symfony's attributes to tag it and alias it so it replaces the default builder.

The `AdminMenuBuilderInterface` requires you to implement a `buildMenu()` method that returns an array of `Menu` objects.

### Example

Here is an example of a custom menu builder:

```php
<?php

namespace App\Component\AdminMenu;

use Aropixel\AdminBundle\Component\Menu\Builder\AdminMenuBuilderInterface;
use Aropixel\AdminBundle\Component\Menu\Model\Link;
use Aropixel\AdminBundle\Component\Menu\Model\Menu;
use Aropixel\AdminBundle\Component\Menu\Model\SubMenu;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('admin_menu_builder')]
#[AsAlias(id: AdminMenuBuilderInterface::class)]
class CustomAdminMenuBuilder implements AdminMenuBuilderInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function buildMenu(): array
    {
        $additionalMenus = [];

        // Menu accessible to ROLE_CONTENT_EDITOR and above
        if ($this->security->isGranted('ROLE_CONTENT_EDITOR')) {
            $additionalMenus[] = $this->buildContentMenu();
        }

        // Menus reserved for ROLE_SUPER_ADMIN
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            $additionalMenus[] = $this->buildAdminMenu();
        }

        return $additionalMenus;
    }

    private function buildContentMenu(): Menu
    {
        $menu = new Menu('content', 'Content');
        $menu->addItem(new Link('Articles', 'admin_article_index', [], ['icon' => 'fas fa-newspaper']));
        
        // Add a sub-menu
        $subMenu = new SubMenu('Legal', ['icon' => 'fas fa-balance-scale'], 'legal');
        $subMenu->addItem(new Link('Terms of Service', 'admin_legals_tos', [], ['icon' => 'fas fa-file-contract']));
        $subMenu->addItem(new Link('Privacy Policy', 'admin_legals_privacy', [], ['icon' => 'fas fa-shield-alt']));
        
        $menu->addItem($subMenu);

        return $menu;
    }

    private function buildAdminMenu(): Menu
    {
        $menu = new Menu('admin', 'Administration');
        $menu->addItem(new Link('Users', 'aropixel_admin_user_index', [], ['icon' => 'fas fa-users-cog']));
        return $menu;
    }
}
```

## Menu Models

There are three main classes you will use to build your menu:

### Menu
A `Menu` represents a top-level category in the admin sidebar.
- `__construct(string $id, string $label)`
- `addItem(ItemInterface $item)`: Adds a link or a sub-menu.

### Link
A `Link` represents a single menu item that points to a route.
- `__construct(string $label, string $routeName, array $routeParameters = [], array $properties = [], string $id = null)`
- The `properties` array is often used to define an icon (e.g., `['icon' => 'fas fa-user']`).

### SubMenu
A `SubMenu` allows you to group multiple links under a collapsible parent.
- `__construct(string $label, array $properties, string $id = null)`
- `addItem(ItemInterface $item)`: Adds a link to the sub-menu.
- `setDefaultChild(Link $defaultChild)`: Sets the link to be used if the sub-menu itself is clicked.

## Quick Menu Customization

The Quick Menu provides shortcuts that are typically displayed on the dashboard or in the fullscreen menu. Like the main menu, it can be customized by implementing the `QuickMenuBuilderInterface`.

### Custom Quick Menu Builder

To create a custom quick menu builder, implement `Aropixel\AdminBundle\Component\Menu\Builder\QuickMenuBuilderInterface`. Use Symfony attributes to tag and alias it.

**Important**: The default Twig template for the quick menu uses the array keys (1 to 5) to apply specific CSS classes and colors. If you want to maintain the original styling, ensure your return array uses these keys.

### Example

```php
<?php

namespace App\Component\AdminMenu;

use Aropixel\AdminBundle\Component\Menu\Builder\QuickMenuBuilderInterface;
use Aropixel\AdminBundle\Component\Menu\Model\Link;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouterInterface;

#[AutoconfigureTag('admin_menu_builder')]
#[AutoconfigureTag('quick_menu_builder')]
#[AsAlias(id: QuickMenuBuilderInterface::class)]
class CustomQuickMenuBuilder implements QuickMenuBuilderInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly Security $security,
    ) {
    }

    public function buildMenu(): array
    {
        $quickMenu = [];

        // Key 1: Light Blue background
        if ($this->routeExists('app_custom_route')) {
            $quickMenu[1] = new Link('Custom', 'app_custom_route', [], ['icon' => 'fas fa-star']);
        }

        // Key 2: Orange background
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $quickMenu[2] = new Link('Admin', 'admin_dashboard', [], ['icon' => 'fas fa-lock']);
        }

        return $quickMenu;
    }

    private function routeExists(string $name): bool
    {
        return !(null === $this->router->getRouteCollection()->get($name));
    }
}
```

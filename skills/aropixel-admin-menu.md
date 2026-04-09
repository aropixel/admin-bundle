---
name: aropixel-admin-menu
description: >
  Manages the Aropixel AdminBundle administration menu.
  Use this skill when the user wants to add an entry in the admin menu,
  create a new menu section, or modify the administration interface navigation.
---

# Skill: Admin Menu AropixelAdminBundle

## File to modify

The project's admin menu is defined in a class that implements `AdminMenuBuilderInterface`.

To find it, you can search for the class that implements this interface in the project (usually located in `src/Component/AdminMenu/` or `src/Menu/`). It builds the menu via private methods per section.

## Current menu structure

The menu is organized into sections (private methods):
- `buildMainMenu()` → **Main Section**: General entities
- `buildSettingsMenu()` → **Settings**: Configuration, Users
- `buildAdminMenu()` → **Administration** (ROLE_SUPER_ADMIN only): Administrators

## Adding a link in an existing section

Add a `new Link(...)` in the corresponding method:

```php
$menu->addItem(new Link('Label displayed', 'index_route_name', [], ['icon' => 'fas fa-icon']));
```

Example — adding "Categories" in the Main section:

```php
private function buildMainMenu(): Menu
{
    $menu = new Menu('main', 'Main');
    $menu->addItem(new Link('Items', 'admin_item_index', [], ['icon' => 'fas fa-list-ul']));
    $menu->addItem(new Link('Categories', 'admin_category_index', [], ['icon' => 'fas fa-tags']));
    return $menu;
}
```

## Creating a new menu section

1. Add a private method `buildXxxMenu()`:

```php
private function buildXxxMenu(): Menu
{
    $menu = new Menu('xxx', 'Section Name');
    $menu->addItem(new Link('My Entity', 'admin_myentity_index', [], ['icon' => 'fas fa-list-ul']));
    return $menu;
}
```

2. Call it in `buildMenu()`:

```php
public function buildMenu(): array
{
    $additionalMenus = [];
    $additionalMenus[] = $this->buildMainMenu();
    $additionalMenus[] = $this->buildXxxMenu(); // <-- add here
    // ...
    return $additionalMenus;
}
```

## Adding a sub-menu (SubMenu)

```php
$subMenu = new SubMenu('Sub-section', ['icon' => 'fas fa-folder'], 'sub-menu-id');
$subMenu->addItem(new Link('Item 1', 'admin_item1_index', [], ['icon' => 'fas fa-file']));
$subMenu->addItem(new Link('Item 2', 'admin_item2_index', [], ['icon' => 'fas fa-file']));
$menu->addItem($subMenu);
```

## Available models

| Class | Usage |
|--------|-------|
| `Menu` | Main sidebar section — `new Menu(string $id, string $label)` |
| `Link` | Simple link to a route — `new Link(string $label, string $routeName, array $routeParams, array $properties)` |
| `SubMenu` | Collapsible group — `new SubMenu(string $label, array $properties, string $id)` |

The `icon` property expects a FontAwesome class: `'fas fa-...'`.

## Necessary imports

```php
use Aropixel\AdminBundle\Component\Menu\Model\Link;
use Aropixel\AdminBundle\Component\Menu\Model\Menu;
use Aropixel\AdminBundle\Component\Menu\Model\SubMenu; // if needed
```

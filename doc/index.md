# Aropixel Admin Bundle

<div align="center">
    <img width="100" height="100" src="assets/logo-aro.png" alt="aropixel logo" />
</div>

## Philosophy

The philosophy of `AropixelAdminBundle` is not to offer a tool that does everything for you and can be fully customized, like `EasyAdminBundle`. Instead, it is a **toolbox** designed to build an administration interface very quickly while leaving the developer free to customize every detail. It acts as a facilitator: a framework with powerful tools.

CRUD tasks are repetitive, so the bundle provides a `make:crud` command. However, instead of generating a CRUD from an entity, it uses a `FormType` already established by the developer as a starting point. This ensures that the developer stays in control of the data mapping and form logic.

***

## Presentation

Aropixel Admin Bundle is a bootstrap admin bundle for your Symfony 7 projects. 
It provides a minimalist admin system with: login, logout, admin users crud, admin menu management.


Our suite of tools consists of several modules, each dedicated to specific aspects of website administration:

* **AdminBundle**: Facilitates the publication and management of news, with advanced features such as publication scheduling and category management.


* **BlogBundle**: Allows the construction of a custom administration interface tailored to the specific needs of the project. It also enables user management and adjustment of permissions according to defined profiles.


* **PageBundle**: Offers the ability to intuitively create, modify, move, or delete pages and subpages, allowing for an evolving site structure.


* **MenuBundle**: Provides a comprehensive system for managing website navigation menus, including nested structures and different locations (header, footer, etc.).




> [NOTE] <br>
AropixelAdminBundle is optimized to work with Symfony 6/7 and PHP 8.2 and above. <br>
Using it with earlier versions is highly likely to cause errors or incompatibilities.


## Key Features

* **Easy Installation and Configuration**: 
> **Seamless Integration**: Easily integrates with Symfony projects, ensuring a smooth setup process.
<br> **Pre-configured Settings**: Out-of-the-box settings that can be customized to fit specific project requirements.   

***

* **User Management**: 
> **Admin User CRUD**: Full create, read, update, and delete functionality for managing admin users.
<br> **Role-Based Access Control**: Define and manage user roles and permissions to restrict access to specific sections of the admin panel.

***

* **Content Management**: 
> **News Management**: Customizable administration interface for managing blog posts, news, and categories.
Customizable administration interface for managing blog posts, comments, and categories

***

* **Page Management**: 
> **Intuitive Page Editor**: Create, modify, move, and delete pages and subpages with a simple interface.


***

* **Menu Management**: 
> **Header and Footer Management**: Easily manage and organize the site’s navigation menus, including headers and footers.
<br> **Dynamic Menu Links**: Add, edit, and rearrange menu links to reflect the site’s structure and content priorities.


***

* **Extensibility**: 
> **Modular Architecture**: Each feature is encapsulated in a module, making it easy to extend or replace functionality.
<br> **Customizable Workflows**: Tailor the admin interface and workflows to meet the specific needs of different projects.

***

* **Miscellaneous**: 
> **Multi-language Support**: Easily add multiple languages to the admin panel to cater to a global audience.


## Further documentation

### 🚀 Getting Started
* [Installation](installation.md)
* [Create User Command](create_user.md)
* [Internationalisation (i18n)](i18n.md)

### 🛠 Tools & Generators
* [CRUD Generator (`make:crud`)](make_crud.md)
* [DataTable Component](datatable.md)

### 📝 Forms & Customization
* [Custom Form Types](forms.md)
* [Form Templates](form_templates.md)
* [CSS Customization](css_customization.md)
* [Select2 Component](select2.md)
* [Entity Customization](entities.md)
* [Admin Menu Customization](admin_menu.md)

### 📦 Bundles & Modules
* [AdminBundle](adminbundle.md)
* [BlogBundle](blogbundle.md)
* [PageBundle](pagebundle.md)
* [MenuBundle](menubundle.md)

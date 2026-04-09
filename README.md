# Aropixel Admin Bundle

<div align="center">
    <img width="100" height="100" src="doc/assets/logo-aro.png" alt="aropixel logo" />
</div>

## Presentation

The `AropixelAdminBundle` is built with a specific philosophy: it is a **toolbox** for developers, not a black-box framework like `EasyAdminBundle`. It provides the framework and tools to build an admin interface quickly while ensuring you retain full control over your code.

As a facilitator, it helps automate repetitive CRUD tasks through a custom `make:crud` generator that starts from your own `FormType`.


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

Discover more by reading the docs:

* [Getting started with AropixelAdminBundle](installation.md)
* [Discover the main tool](adminbundle.md)
* [Manage blog content](blogbundle.md)
* [Create and order pages](pagebundle.md)
* [Store and send incoming messages](blogbundle.md)
* [Manage header and footer](menubundle.md)

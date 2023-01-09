<p align="center">
  <a href="http://www.aropixel.com/">
    <img src="https://avatars1.githubusercontent.com/u/14820816?s=200&v=4" alt="Aropixel logo" width="75" height="75" style="border-radius:100px">
  </a>
</p>


<h1 align="center">Aropixel Admin Bundle</h1>

<p>
  Aropixel Admin Bundle is a bootstrap admin bundle for your Symfony 4 projects. It provides a minimalist admin system with: login, logout, admin users crud, admin menu management.<br />
  You can plug <a href="https://github.com/aropixel">compatible bundles</a> to manage:
  <ul>
    <li><a href="https://github.com/aropixel/blog-bundle">blog</a> content</li>
    <li><a href="https://github.com/aropixel/page-bundle">pages</a> of your website</li>
    <li><a href="https://github.com/aropixel/menu-bundle">menus</a> of your website</li>
    <li>store and send incoming <a href="https://github.com/aropixel/menu-bundle">contacts</a> of your website</li>
  </ul>  
</p>


![GitHub last commit](https://img.shields.io/github/last-commit/aropixel/admin-bundle.svg)
[![GitHub issues](https://img.shields.io/github/issues/aropixel/admin-bundle.svg)](https://github.com/stisla/stisla/issues)
[![License](https://img.shields.io/github/license/aropixel/admin-bundle.svg)](LICENSE)

![Aropixel Admin Preview](./screenshot.png)


## Table of contents

- [Quick start](#quick-start)
- [License](#license)


## Quick start

- Create your symfony 4 project
- Require Aropixel Admin Bundle : `composer require aropixel/admin-bundle`
- If you get a "knplabs/knp-paginator-bundle" error, downgrade twig to version 2:  `composer require twig/twig ^2.0` and re-install the AdminBundle
- Apply migrations
- Create a "aropixel.yaml" file in config folder and configure according to you need:
````
aropixel_admin:
    client:
        name: "aropixel client"
    copyright:
        name: "Aropixel"
        link: "http://www.aropixel.com"
    theme:
        menu_position: left
````
- Configure the security.yaml:
````
security:

    providers:
        admin_user_provider:
            entity:
                class: Aropixel\AdminBundle\Domain\Entity\User
                property: email

    encoders:
        Aropixel\AdminBundle\Domain\Entity\User:
            algorithm: argon2i
            cost: 12

    role_hierarchy:
        ROLE_USER:        [ROLE_USER]
        ROLE_ADMIN:       [ROLE_ADMIN]
        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]
        ROLE_HYPER_ADMIN: [ROLE_SUPER_ADMIN, ROLE_ALLOWED_TO_SWITCH]

    firewalls:
        backoffice:
            context: primary_auth
            pattern:            ^/admin
            form_login:
                provider:       admin_user_provider
                login_path:     aropixel_admin_security_login
                use_forward:    true
                use_referer:    true
                check_path:     aropixel_admin_security_check
                failure_path:   aropixel_admin_security_login
                default_target_path: _admin
            remember_me:
                secret:   '%kernel.secret%'
                lifetime: 2592000 # 1 month in seconds
                path:     /admin
            logout:
                path: aropixel_admin_security_logout
                target: aropixel_admin_security_login
            anonymous:    true
            guard:
                provider: admin_user_provider
                authenticators:
                    - Aropixel\AdminBundle\Security\LoginFormAuthenticator

        dev:
            pattern:  ^/(_(profiler|wdt)|css|images|js)/
            security: false

    access_control:
        - { path: ^/admin/login$, role: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/admin/, role: ROLE_ADMIN }

````
- Include the routes:
````
aropixel_admin:
    resource: '@AropixelAdminBundle/Resources/config/routing/aropixel.yml'
    prefix: /admin

````
- Create your first admin access : php bin/console aropixel:admin:setup

- Add the ConfigureMenuListener class in App Folder and register it as service


## License
Aropixel Admin Bundle is under the [MIT License](LICENSE)

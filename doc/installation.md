# Installation

## Quick start with Castor Starter

If you use [aropixel/castor-starter](https://github.com/aropixel/castor-starter), you can bootstrap a full Symfony project with the AdminBundle already installed and configured in a single command — no manual setup required:

```bash
castor-starter aropixel:new:admin
```

---

## Manual installation

We provide several ways to install our AdminBundle, depending on your needs:

- Create your Symfony 6.4, 7.* or 8.* project
- Require Aropixel Admin Bundle
```bash
composer require aropixel/admin-bundle
```
- Apply migrations
- Create a "aropixel.yaml" file in config folder and configure according to you need:

````
aropixel_admin:
    client:
        name: "Aropixel Client"
    copyright:
        name: "Aropixel"
        link: "http://www.aropixel.com"
    theme:
        logo:
            path: "bundles/aropixeladmin/img/logo.png"
            width: "150px"
            menu:
                path: "bundles/aropixeladmin/img/logo-opened-menu.gif"
                width: "50px"
            login:
                path: "bundles/aropixeladmin/img/sigle_fond-blanc_code-transparent.png"
        colors:
            background_color: "#0CABA8"
            btn_background_color: "#0CABA8"
            btn_color: "#fff"
        images:
            placeholder_img_path: "bundles/aropixeladmin/img/logo-vert.png"
            login_img: ""
````
- Configure the security.yaml:
````
security:
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'
        harsh:
            algorithm: auto
            cost: 15

    providers:
        admin_user_provider:
            entity:
                class: Aropixel\AdminBundle\Entity\User
                property: email

    role_hierarchy:
        ROLE_USER:        [ROLE_USER]
        ROLE_ADMIN:       [ROLE_ADMIN]
        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]
        ROLE_HYPER_ADMIN: [ROLE_SUPER_ADMIN, ROLE_ALLOWED_TO_SWITCH]

    firewalls:
        backoffice:
            context: primary_auth
            pattern: ^/
            form_login:
                provider: admin_user_provider
                login_path: aropixel_admin_security_login
                use_forward: true
                use_referer: true
                check_path: aropixel_admin_security_check
                failure_path: aropixel_admin_security_login
                default_target_path: dashboard
            remember_me:
                secret: '%kernel.secret%'
                lifetime: 2592000 # 1 month in seconds
                path: /
            logout:
                path: aropixel_admin_security_logout
                target: aropixel_admin_security_login
            #anonymous: true
            entry_point: Aropixel\AdminBundle\Component\Security\LoginFormAuthenticator
            custom_authenticators:
                - Aropixel\AdminBundle\Component\Security\LoginFormAuthenticator
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

    access_control:
        - { path: ^/admin/login$, role: PUBLIC_ACCESS }
        - { path: ^/admin/reset/, role: PUBLIC_ACCESS }
        - { path: ^/admin, role: ROLE_ADMIN }
````
- Include the routes:
````yaml
# config/routes.yaml

# Administration routes
aropixel_admin:
    resource: '@AropixelAdminBundle/Resources/config/routing/aropixel.yml'
    prefix: /admin

# Public download route (must be outside of protected prefix)
aropixel_admin_download:
    resource: '@AropixelAdminBundle/Resources/config/routes/download.yaml'
````
- Create your first admin access : php bin/console aropixel:admin:create-user

- Add the ConfigureMenuListener class in Src Folder and register it as service

````
then add "@aropixel/admin-bundle" to your importmap.php
````
'@aropixel/admin-bundle' => [
    'path' => './vendor/aropixel/admin-bundle/assets/loader.js',
    'entrypoint' => true,
]
````

## Icons

The admin renders its icons with [symfony/ux-icons](https://symfony.com/bundles/ux-icons/current/index.html),
using the [Lucide](https://lucide.dev) set (`{{ ux_icon('lucide:...') }}`). Both
`symfony/ux-icons` and `symfony/http-client` are pulled in as dependencies of the bundle.

In development, icons are fetched on demand from the Iconify API and cached. **For
production, import them locally once so no HTTP call is made at render time:**

````bash
php bin/console ux:icons:lock
````

This scans your templates (the bundle's included) and writes the used SVGs into
`assets/icons/`. Run it in your build/deploy pipeline. It is the only icon-related step an
integrator has to take.

To override a bundle icon with your own, drop an SVG at the matching path in your app's
`assets/icons/` — local icons take precedence over the on-demand ones.

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
- [Translations](#translations)
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
                class: Aropixel\AdminBundle\Entity\User
                property: email

    encoders:
        Aropixel\AdminBundle\Entity\User:
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


## Translations

- Configure translations

````
parameters:
    locale: 'fr'
    locales: ['fr', 'en']

form.type.translatable:
    class: Aropixel\AdminBundle\Form\Type\TranslatableType
    arguments: [ '@doctrine.orm.default_entity_manager', '@validator', '@parameter_bag' ]
    tags:
        - { name: form.type, alias: translatable }
````

- Create Entity to translate:
````
<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;

#[ORM\Table(name: 'article')]
#[ORM\Entity]
#[Gedmo\TranslationEntity(class: ArticleTranslation::class)]
class Article implements Translatable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Gedmo\Translatable]
    #[ORM\Column(name: 'title', type: 'string', length: 128, nullable: true)]
    private $title;

    /**
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    #[Gedmo\Locale]
    private $locale;

    #[ORM\OneToMany(targetEntity: ArticleTranslation::class, mappedBy: 'object', cascade: ['persist', 'remove'])]
    private $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    public function addTranslation(ArticleTranslation $t)
    {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
    }

    public function getId()
    {
        return $this->id;
    }
    
    ...

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getLocales()
    {
        $languages = [];

        foreach ($this->getTranslations() as $translation) {
            if (!in_array($translation->getLocale(), $languages)) {
                $languages[] = $translation->getLocale();
            }
        }

        return implode(", ", $languages);
    }

}
````

````
<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;

#[ORM\Table(name: 'article_translation')]
#[ORM\Index(name: 'article_translation_idx', columns: ['locale', 'object_id', 'field'])]
#[ORM\Entity(repositoryClass: TranslationRepository::class)]
class ArticleTranslation extends AbstractPersonalTranslation
{
    public function __construct($locale, $field, $value)
    {
        $this->setLocale($locale);
        $this->setField($field);
        $this->setContent($value);
    }

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'object_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $object;
}
````

- Add form:
````
<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\ArticleTranslation;
use Aropixel\AdminBundle\Form\Type\TranslatableType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TranslatableType::class, [
                'label'                => 'Titre',
                'personal_translation' => ArticleTranslation::class,
                'property_path'        => 'translations'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
````

- Add a classic controller : 

````
 #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ArticleRepository $articleRepository): Response
    {

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articleRepository->save($article, true);

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/form.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

````

- And include your form in twig, foreach field :

````
{% include '@AropixelAdmin/Form/translatable_field.html.twig' with {'children': form.title.children} %}
````


## License
Aropixel Admin Bundle is under the [MIT License](LICENSE)

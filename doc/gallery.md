# Image Gallery

This guide explains how to attach a multi-image gallery to any entity using `GalleryType`.

The gallery widget provides:
- Drag-and-drop reordering (via Gedmo `SortablePosition`)
- Selection from the shared media library
- Direct upload
- Optional per-image fields: title, description, link
- Optional image cropping

## Summary

- [How it works](#how-it-works)
- [Setup (without crops)](#setup-without-crops)
    - [1. Create the parent entity](#1-create-the-parent-entity)
    - [2. Create the image entity](#2-create-the-image-entity)
    - [3. Create the repositories](#3-create-the-repositories)
    - [4. Create the form type](#4-create-the-form-type)
    - [5. Create the controller](#5-create-the-controller)
    - [6. Create the templates](#6-create-the-templates)
    - [7. Create the migration](#7-create-the-migration)
- [Setup with crops](#setup-with-crops)
- [GalleryType options reference](#gallerytype-options-reference)

---

## How it works

The gallery relies on two entities:

- A **parent entity** (e.g., `Gallery`) that holds the metadata (title, status…). It declares a `OneToMany` relation to the image entity.
- An **image entity** (e.g., `GalleryImage`) that extends `AttachedImage`. Each row represents one image slot in the gallery and carries its position, optional title/description/link, and a `ManyToOne` back to the parent.

`AttachedImage` already provides `position` with `#[Gedmo\SortablePosition]`. Adding `#[Gedmo\SortableGroup]` on the foreign key to the parent ensures positions are scoped per gallery — each gallery has its own sequence starting at 0.

---

## Setup (without crops)

### 1. Create the parent entity

```php
// src/Entity/Gallery.php
namespace App\Entity;

use Aropixel\AdminBundle\Entity\Publishable;
use Aropixel\AdminBundle\Entity\PublishableTrait;
use App\Repository\GalleryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: GalleryRepository::class)]
#[ORM\Table(name: 'app_gallery')]
class Gallery implements Publishable
{
    use PublishableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = Publishable::STATUS_OFFLINE;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $slug = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTime $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt = null;

    /** @var Collection<int, GalleryImage> */
    #[ORM\OneToMany(
        targetEntity: GalleryImage::class,
        mappedBy: 'gallery',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    // --- standard getters/setters ---

    public function getId(): ?int { return $this->id; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getSlug(): ?string { return $this->slug; }
    public function getCreatedAt(): ?\DateTime { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTime { return $this->updatedAt; }

    /** @return Collection<int, GalleryImage> */
    public function getImages(): Collection { return $this->images; }

    public function addImage(GalleryImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setGallery($this);
        }
        return $this;
    }

    public function removeImage(GalleryImage $image): self
    {
        if ($this->images->removeElement($image) && $image->getGallery() === $this) {
            $image->setGallery(null);
        }
        return $this;
    }
}
```

> **Note:** `orphanRemoval: true` ensures that images removed from the gallery through the form are automatically deleted from the database.

> **Note:** Never call `setSlug()` — Gedmo generates it automatically on the first `flush()`.

---

### 2. Create the image entity

The image entity extends `AttachedImage` (which provides `image`, `position`, `title`, `description`, `link`, and attribute fields).

`#[Gedmo\SortableGroup]` on the `gallery` relation scopes the position sequence per gallery.

```php
// src/Entity/GalleryImage.php
namespace App\Entity;

use Aropixel\AdminBundle\Entity\AttachedImage;
use App\Repository\GalleryImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: GalleryImageRepository::class)]
#[ORM\Table(name: 'app_gallery_image')]
class GalleryImage extends AttachedImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: Gallery::class, inversedBy: 'images')]
    #[ORM\JoinColumn(name: 'gallery_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Gallery $gallery = null;

    public function getId(): ?int { return $this->id; }

    public function getGallery(): ?Gallery { return $this->gallery; }

    public function setGallery(?Gallery $gallery): self
    {
        $this->gallery = $gallery;
        return $this;
    }
}
```

> **Note:** `AttachedImage::setImage()` saves the previous image reference internally to detect changes. Never call `setOldImage()` manually.

---

### 3. Create the repositories

```php
// src/Repository/GalleryRepository.php
namespace App\Repository;

use App\Entity\Gallery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GalleryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gallery::class);
    }
}
```

```php
// src/Repository/GalleryImageRepository.php
namespace App\Repository;

use App\Entity\GalleryImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GalleryImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GalleryImage::class);
    }
}
```

---

### 4. Create the form type

Use `GalleryType` from the bundle for the `images` field. Map it to `GalleryImage::class` via `image_class`.

> **Naming:** Avoid naming your form class `GalleryType` — it conflicts with the bundle's `Aropixel\AdminBundle\Form\Type\Image\Gallery\GalleryType`. Use a more specific name (e.g., `GalleryAdminType`).

```php
// src/Form/GalleryAdminType.php
namespace App\Form;

use Aropixel\AdminBundle\Form\Type\EditorType;
use Aropixel\AdminBundle\Form\Type\Image\Gallery\GalleryType as GalleryWidgetType;
use Aropixel\AdminBundle\Form\Type\Page\PublishableType;
use App\Entity\Gallery;
use App\Entity\GalleryImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GalleryAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('publishable', PublishableType::class, ['mapped' => false, 'inherit_data' => true])
            ->add('images', GalleryWidgetType::class, [
                'image_class' => GalleryImage::class,
                'fields' => [
                    'title' => true,
                    'description' => true,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Gallery::class]);
    }
}
```

The `fields` option enables optional per-image text fields displayed as a popover in the widget:

| Key | Default | Description |
|---|---|---|
| `title` | `false` | Per-image title |
| `description` | `false` | Per-image description |
| `link` | `false` | Per-image hyperlink |

---

### 5. Create the controller

```php
// src/Controller/Admin/GalleryController.php
namespace App\Controller\Admin;

use App\Entity\Gallery;
use App\Form\GalleryAdminType;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/gallery', name: 'admin_gallery_')]
class GalleryController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(DataTableFactory $dataTableFactory): Response
    {
        return $dataTableFactory
            ->create(Gallery::class)
            ->setColumns([
                ['label' => 'Title', 'orderBy' => 'title'],
                ['label' => 'Status', 'orderBy' => 'status'],
                ['label' => '', 'orderBy' => '', 'class' => 'no-sort'],
            ])
            ->searchIn(['title'])
            ->renderJson(fn (Gallery $gallery) => [
                $gallery->getTitle(),
                $gallery->getStatus(),
                $this->renderView('admin/gallery/_actions.html.twig', ['item' => $gallery]),
            ])
            ->render('admin/gallery/index.html.twig');
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $gallery = new Gallery();
        $form = $this->createForm(GalleryAdminType::class, $gallery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($gallery);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_gallery_edit', ['id' => $gallery->getId()]);
        }

        return $this->render('admin/gallery/form.html.twig', [
            'gallery' => $gallery,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Gallery $gallery): Response
    {
        $form = $this->createForm(GalleryAdminType::class, $gallery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.saved'));
            return $this->redirectToRoute('admin_gallery_edit', ['id' => $gallery->getId()]);
        }

        return $this->render('admin/gallery/form.html.twig', [
            'gallery' => $gallery,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Gallery $gallery): Response
    {
        if ($this->isCsrfTokenValid('delete'.$gallery->getId(), $request->request->get('_token'))) {
            $this->em->remove($gallery);
            $this->em->flush();
            $this->addFlash('notice', $this->translator->trans('generic.flash.deleted'));
        }

        return $this->redirectToRoute('admin_gallery_index');
    }
}
```

---

### 6. Create the templates

**`templates/admin/gallery/index.html.twig`**

```twig
{% extends '@AropixelAdmin/List/datatable.html.twig' %}
{% import '@AropixelAdmin/Macro/breadcrumb.html.twig' as nav %}

{% block meta_title %}{{ 'text.list'|trans }}{% endblock %}
{% block header_title %}Galleries{% endblock %}

{% block header_breadcrumb %}
    {{ nav.breadcrumbs([
        { label: 'text.home'|trans, url: url('_admin') },
        { label: 'Galleries' }
    ]) }}
{% endblock %}
```

**`templates/admin/gallery/_actions.html.twig`**

```twig
{% import '@AropixelAdmin/Macro/actions.html.twig' as list %}
<td class="text-right">
    {{ list.actions(item, path('admin_gallery_edit', {id: item.id}), path('admin_gallery_delete', {id: item.id})) }}
</td>
```

**`templates/admin/gallery/form.html.twig`**

```twig
{% extends '@AropixelAdmin/Form/base.html.twig' %}
{% import '@AropixelAdmin/Macro/breadcrumb.html.twig' as nav %}

{% set is_edit = gallery.id is not null %}
{% set title = is_edit ? 'text.edit'|trans ~ ' : ' ~ gallery.title : 'text.add'|trans %}

{% block meta_title %}{{ title }}{% endblock %}
{% block header_title %}{{ title }}{% endblock %}

{% block header_breadcrumb %}
    {{ nav.breadcrumbs([
        { label: 'text.home'|trans, url: url('_admin') },
        { label: 'Galleries', url: path('admin_gallery_index') },
        { label: gallery.id ? 'text.edit'|trans : 'text.add'|trans }
    ]) }}
{% endblock %}

{% block formbody %}
    {{ form_rest(form) }}
{% endblock %}
```

---

### 7. Create the migration

```php
// migrations/VersionXXXXXXXXXXXXXX.php
public function up(Schema $schema): void
{
    $this->addSql('CREATE TABLE app_gallery (
        id INT AUTO_INCREMENT NOT NULL,
        title VARCHAR(255) NOT NULL,
        status VARCHAR(20) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME DEFAULT NULL,
        UNIQUE INDEX UNIQ_app_gallery_slug (slug),
        PRIMARY KEY (id)
    ) DEFAULT CHARACTER SET utf8mb4');

    $this->addSql('CREATE TABLE app_gallery_image (
        id INT AUTO_INCREMENT NOT NULL,
        title VARCHAR(255) DEFAULT NULL,
        link VARCHAR(255) DEFAULT NULL,
        description LONGTEXT DEFAULT NULL,
        attr_title VARCHAR(255) DEFAULT NULL,
        attr_alt VARCHAR(255) DEFAULT NULL,
        attr_class VARCHAR(255) DEFAULT NULL,
        position INT NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME DEFAULT NULL,
        image_id INT DEFAULT NULL,
        gallery_id INT DEFAULT NULL,
        INDEX IDX_app_gallery_image_image (image_id),
        INDEX IDX_app_gallery_image_gallery (gallery_id),
        PRIMARY KEY (id)
    ) DEFAULT CHARACTER SET utf8mb4');

    $this->addSql('ALTER TABLE app_gallery_image
        ADD CONSTRAINT FK_app_gallery_image_image FOREIGN KEY (image_id) REFERENCES aropixel_image (id),
        ADD CONSTRAINT FK_app_gallery_image_gallery FOREIGN KEY (gallery_id) REFERENCES app_gallery (id) ON DELETE CASCADE
    ');
}

public function down(Schema $schema): void
{
    $this->addSql('ALTER TABLE app_gallery_image
        DROP FOREIGN KEY FK_app_gallery_image_image,
        DROP FOREIGN KEY FK_app_gallery_image_gallery
    ');
    $this->addSql('DROP TABLE app_gallery_image');
    $this->addSql('DROP TABLE app_gallery');
}
```

> **Tip:** Rather than writing the migration by hand, let Doctrine generate it after creating the entities:
> ```bash
> bin/console doctrine:migrations:diff
> ```

---

## Setup with crops

To enable per-image cropping, three additional steps are needed compared to the basic setup.

### 1. Create the crop entity

```php
// src/Entity/GalleryImageCrop.php
namespace App\Entity;

use Aropixel\AdminBundle\Entity\AttachedImageInterface;
use Aropixel\AdminBundle\Entity\Crop;
use App\Repository\GalleryImageCropRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GalleryImageCropRepository::class)]
#[ORM\Table(name: 'app_gallery_image_crop')]
class GalleryImageCrop extends Crop
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GalleryImage::class, inversedBy: 'crops')]
    private ?GalleryImage $image = null;

    public function getId(): ?int { return $this->id; }

    public function getImage(): AttachedImageInterface { return $this->image; }

    public function setImage(?GalleryImage $image): self
    {
        $this->image = $image;
        return $this;
    }
}
```

### 2. Update the image entity

Add `CroppableInterface`, `CroppableTrait`, and the `$crops` collection:

```php
// src/Entity/GalleryImage.php
use Aropixel\AdminBundle\Entity\AttachedImage;
use Aropixel\AdminBundle\Entity\CroppableInterface;
use Aropixel\AdminBundle\Entity\CroppableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: GalleryImageRepository::class)]
#[ORM\Table(name: 'app_gallery_image')]
class GalleryImage extends AttachedImage implements CroppableInterface
{
    use CroppableTrait;

    // ... id and gallery fields as before ...

    /** @var Collection<int, GalleryImageCrop> */
    #[ORM\OneToMany(targetEntity: GalleryImageCrop::class, mappedBy: 'image', cascade: ['persist', 'remove'])]
    private Collection $crops;

    public function __construct()
    {
        $this->crops = new ArrayCollection();
    }

    public function addCrop(GalleryImageCrop $crop): self
    {
        if (!$this->crops->contains($crop)) {
            $this->crops[] = $crop;
            $crop->setImage($this);
        }
        return $this;
    }

    public function removeCrop(GalleryImageCrop $crop): self
    {
        if ($this->crops->removeElement($crop) && $crop->getImage() === $this) {
            $crop->setImage(null);
        }
        return $this;
    }
}
```

`CroppableTrait` provides `getCrops()`, `getImageUid()`, and `getCropsInfos()` — no need to implement them manually.

### 3. Add crops to the form

Pass the `crops` array and `crop_class` to the `GalleryWidgetType`:

```php
->add('images', GalleryWidgetType::class, [
    'image_class' => GalleryImage::class,
    'crops' => [
        'gallery_thumbnail' => 'Thumbnail (4/3)',
        'gallery_wide'      => 'Wide banner (16/9)',
    ],
    'crop_class' => GalleryImageCrop::class,
])
```

The keys in `crops` must match Liip Imagine filter names configured in `config/packages/liip_imagine.yaml`.

### 4. Add the crop table to the migration

```sql
CREATE TABLE app_gallery_image_crop (
    id INT AUTO_INCREMENT NOT NULL,
    filter VARCHAR(255) NOT NULL,
    crop VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL,
    image_id INT DEFAULT NULL,
    INDEX IDX_app_gallery_image_crop_image (image_id),
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4;

ALTER TABLE app_gallery_image_crop
    ADD CONSTRAINT FK_app_gallery_image_crop_image
    FOREIGN KEY (image_id) REFERENCES app_gallery_image (id) ON DELETE CASCADE;
```

---

## GalleryType options reference

| Option | Type | Default | Description |
|---|---|---|---|
| `image_class` | `string` | `null` | **Required.** The FQCN of the image entity (extends `AttachedImage`). Also used as the media library category filter. |
| `fields` | `array` | `[]` | Per-image optional fields. Keys: `title`, `description`, `link`. Set to `true` to enable or pass `['label' => '...']` to override the label. |
| `crops` | `array` | `[]` | Map of Liip filter slug → human label. Enables the crop button per image. Requires `CroppableInterface` on the image entity. |
| `crop_class` | `string` | `null` | FQCN of the crop entity (extends `Crop`). Required when `crops` is set and crops are stored in a separate entity. |
| `grid` | `string` | `'col-md-6 col-lg-6'` | Bootstrap grid class applied to each image thumbnail. |
| `image_library` | `string` | `null` | FQCN used to filter the media library. Defaults to `image_class`. |
| `accept` | `string` | `null` | MIME types accepted by the upload input (e.g., `'image/png,image/jpeg'`). |
| `max_size` | `int` | `null` | Maximum upload size in bytes. |

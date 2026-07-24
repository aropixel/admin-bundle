---
name: aropixel-gallery
description: >
  Attach a multi-image gallery to a Symfony entity using AropixelAdminBundle's GalleryType.
  Use this skill whenever the user asks to add an image gallery, a photo slideshow,
  a collection of images, or multiple pictures to an entity.
  Covers the full workflow: parent entity, image entity extending AttachedImage,
  form integration with GalleryType, controller with addImage/removeImage,
  templates, migration, and optional crop support per image.
  Also use when the user asks to enable image reordering, add title/description/link
  fields per image in a gallery, or configure crops on gallery images.
---

# Skill: Image Gallery with AropixelAdminBundle

## How it works

Two entities are needed:
- A **parent entity** (e.g. `Gallery`) — holds metadata, declares `OneToMany` to the image entity.
- An **image entity** (e.g. `GalleryImage`) — extends `AttachedImage`, one row per image, carries position and a `ManyToOne` back to the parent.

`AttachedImage` already provides `#[Gedmo\SortablePosition]` on `position`. Adding `#[Gedmo\SortableGroup]` on the FK to the parent scopes position sequences per parent (each gallery has its own 0, 1, 2…).

---

## Complete setup (without crops)

### 1 — Parent entity

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
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 20)]
    private string $status = Publishable::STATUS_OFFLINE;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(length: 255, unique: true)]
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

    public function __construct() { $this->images = new ArrayCollection(); }

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

> **Invariants:**
> - `PublishableTrait` does NOT declare `$status` — it must be on the concrete class.
> - Never call `setSlug()` — Gedmo generates it on `flush()`.
> - `orphanRemoval: true` deletes removed images from the database automatically.

---

### 2 — Image entity

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
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: Gallery::class, inversedBy: 'images')]
    #[ORM\JoinColumn(name: 'gallery_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Gallery $gallery = null;

    public function getId(): ?int { return $this->id; }
    public function getGallery(): ?Gallery { return $this->gallery; }
    public function setGallery(?Gallery $gallery): self { $this->gallery = $gallery; return $this; }
}
```

> **Invariant:** `AttachedImage::setImage()` saves `$oldImage` internally — never call `setOldImage()` manually.

---

### 3 — Repositories

```php
// src/Repository/GalleryRepository.php
class GalleryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Gallery::class); }
}

// src/Repository/GalleryImageRepository.php
class GalleryImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, GalleryImage::class); }
}
```

---

### 4 — Form type

> **Naming:** Do not name the form class `GalleryType` — it conflicts with `Aropixel\AdminBundle\Form\Type\Image\Gallery\GalleryType`. Use a specific name (e.g. `GalleryAdminType`).

```php
// src/Form/GalleryAdminType.php
namespace App\Form;

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
                    'title'       => true,
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

Optional per-image fields via `fields`: `title`, `description`, `link` (set to `true` to enable, or `['label' => '...']` to override the label).

---

### 5 — Controller

```php
// src/Controller/Admin/GalleryController.php
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
                ['label' => 'Title',  'orderBy' => 'title'],
                ['label' => 'Status', 'orderBy' => 'status'],
                ['label' => '',       'orderBy' => '', 'class' => 'no-sort'],
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

        return $this->render('admin/gallery/form.html.twig', ['gallery' => $gallery, 'form' => $form->createView()]);
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

        return $this->render('admin/gallery/form.html.twig', ['gallery' => $gallery, 'form' => $form->createView()]);
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

### 6 — Templates

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

### 7 — Migration

```sql
CREATE TABLE app_gallery (
    id INT AUTO_INCREMENT NOT NULL,
    title VARCHAR(255) NOT NULL,
    status VARCHAR(20) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL,
    UNIQUE INDEX UNIQ_app_gallery_slug (slug),
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4;

CREATE TABLE app_gallery_image (
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
) DEFAULT CHARACTER SET utf8mb4;

ALTER TABLE app_gallery_image
    ADD CONSTRAINT FK_app_gallery_image_image   FOREIGN KEY (image_id)   REFERENCES aropixel_image (id),
    ADD CONSTRAINT FK_app_gallery_image_gallery  FOREIGN KEY (gallery_id) REFERENCES app_gallery (id) ON DELETE CASCADE;
```

Or: `bin/console doctrine:migrations:diff`

---

## Adding crop support per image

### 1 — Crop entity

```php
// src/Entity/GalleryImageCrop.php
#[ORM\Entity]
#[ORM\Table(name: 'app_gallery_image_crop')]
class GalleryImageCrop extends Crop
{
    #[ORM\Id] #[ORM\GeneratedValue] #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GalleryImage::class, inversedBy: 'crops')]
    private ?GalleryImage $image = null;

    public function getId(): ?int { return $this->id; }
    public function getImage(): AttachedImageInterface { return $this->image; }
    public function setImage(?GalleryImage $image): self { $this->image = $image; return $this; }
}
```

### 2 — Update the image entity

```php
use Aropixel\AdminBundle\Entity\CroppableInterface;
use Aropixel\AdminBundle\Entity\CroppableTrait;

class GalleryImage extends AttachedImage implements CroppableInterface
{
    use CroppableTrait;

    // ... id and gallery fields unchanged ...

    /** @var Collection<int, GalleryImageCrop>|null */
    #[ORM\OneToMany(targetEntity: GalleryImageCrop::class, mappedBy: 'image', cascade: ['persist', 'remove'])]
    protected ?Collection $crops = null;

    public function addCrop(GalleryImageCrop $crop): self
    {
        if (!$this->getCrops()->contains($crop)) { $this->crops[] = $crop; $crop->setImage($this); }
        return $this;
    }

    public function removeCrop(GalleryImageCrop $crop): self
    {
        if ($this->getCrops()->removeElement($crop) && $crop->getImage() === $this) { $crop->setImage(null); }
        return $this;
    }
}
```

### 3 — Form with crops

```php
->add('images', GalleryWidgetType::class, [
    'image_class' => GalleryImage::class,
    'crop_class'  => GalleryImageCrop::class,
    'crops' => [
        'gallery_thumbnail' => 'Thumbnail (4/3)',
        'gallery_wide'      => 'Wide (16/9)',
    ],
])
```

### 4 — Crop table

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

| Option | Default | Description |
|---|---|---|
| `image_class` | `null` | **Required.** FQCN of the image entity (extends `AttachedImage`). Also filters the media library. |
| `fields` | `[]` | Per-image optional fields: `title`, `description`, `link`. Set to `true` or `['label' => '...']`. |
| `crops` | `[]` | Map of Liip filter slug → label. Enables the crop button. Requires `CroppableInterface` on the image entity. |
| `crop_class` | `null` | FQCN of the crop entity (extends `Crop`). Required when `crops` is set. |
| `grid` | `'col-md-6 col-lg-6'` | Bootstrap grid class for each image thumbnail. |
| `image_library` | `null` | FQCN used to filter the media library. Defaults to `image_class`. |
| `accept` | `null` | MIME types for the upload input (e.g. `'image/png,image/jpeg'`). |
| `max_size` | `null` | Maximum upload size in bytes. |

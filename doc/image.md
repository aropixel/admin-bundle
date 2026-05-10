# Single Image

This guide explains how to attach a single image to any entity using `ImageType`.

The image widget provides:
- Upload directly from the form
- Selection from the shared media library
- Optional alt text editing
- Optional image cropping

## Summary

- [How it works](#how-it-works)
- [Quick start: the generator command](#quick-start-the-generator-command)
- [Manual setup (entity mode)](#manual-setup-entity-mode)
    - [1. Create the image entity](#1-create-the-image-entity)
    - [2. Update the parent entity](#2-update-the-parent-entity)
    - [3. Add the field to the form type](#3-add-the-field-to-the-form-type)
    - [4. Create the migration](#4-create-the-migration)
- [Adding crop support](#adding-crop-support)
    - [1. Create the crop entity](#1-create-the-crop-entity)
    - [2. Update the image entity](#2-update-the-image-entity)
    - [3. Configure crops in the form](#3-configure-crops-in-the-form)
    - [4. Add the crop table to the migration](#4-add-the-crop-table-to-the-migration)
- [Filename mode](#filename-mode)
- [ImageType options reference](#imagetype-options-reference)

---

## How it works

`ImageType` operates in two modes:

- **Entity mode** *(recommended)*: The image association is stored in a dedicated entity that extends `AttachedImage`. The parent entity holds a `OneToOne` relation to this image entity.
- **Filename mode**: The image filename is stored directly as a string field on the parent entity. No separate entity is needed, but crop support is limited.

This guide focuses on **entity mode**, which is the standard approach for all Aropixel bundles.

The relationship:

```
YourEntity  (OneToOne, owning)
    └─ YourEntityImage extends AttachedImage  (stores image ref, alt, title, position…)
            └─ YourEntityImageCrop extends Crop  (optional, one row per crop filter)
```

---

## Quick start: the generator command

The bundle provides a command that generates the image entity (and optionally the crop entity) and can automatically update the parent entity:

```bash
bin/console aropixel:make:image
```

The command asks interactively for:

1. **Parent entity class** — e.g. `App\Entity\Artist`
2. **Image property name** — e.g. `image` (produces `ArtistImage`)
3. **Croppable?** — whether to also generate a crop entity (`ArtistImageCrop`)
4. **Auto-update parent?** — whether to insert the relation property and methods directly into the parent entity file

Non-interactive usage with options:

```bash
bin/console aropixel:make:image \
  --parent-class="App\Entity\Artist" \
  --property=image \
  --croppable \
  --auto-update
```

### Generated files

| File | Description |
|---|---|
| `src/Entity/ArtistImage.php` | Image entity extending `AttachedImage` |
| `src/Entity/ArtistImageCrop.php` | *(if croppable)* Crop entity extending `Crop` |

The parent entity is updated (or the code is displayed for manual insertion) with the `OneToOne` property and the `getImage()` / `setImage()` methods.

After running the command, two manual steps remain:
1. [Add the field to the form type](#3-add-the-field-to-the-form-type)
2. [Create the migration](#4-create-the-migration)

---

## Manual setup (entity mode)

### 1. Create the image entity

The image entity extends `AttachedImage`, which provides: `image` (FK to the shared image library), `position`, `title`, `link`, `description`, `attrTitle`, `attrAlt`, `attrClass`, and timestamps.

```php
// src/Entity/ArtistImage.php
namespace App\Entity;

use Aropixel\AdminBundle\Entity\AttachedImage;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'app_artist_image')]
class ArtistImage extends AttachedImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Artist::class, inversedBy: 'image')]
    #[ORM\JoinColumn(name: 'artist_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Artist $artist = null;

    public function getId(): ?int { return $this->id; }

    public function getArtist(): ?Artist { return $this->artist; }

    public function setArtist(?Artist $artist): self
    {
        $this->artist = $artist;
        return $this;
    }
}
```

> **Note:** `AttachedImage::setImage()` saves the previous image reference internally to detect changes — never call `setOldImage()` manually.

---

### 2. Update the parent entity

Add the `OneToOne` relation (inverse side) and the `getImage()` / `setImage()` methods to the parent entity.

```php
// src/Entity/Artist.php

// --- add in the class body ---

#[ORM\OneToOne(targetEntity: ArtistImage::class, mappedBy: 'artist', cascade: ['persist', 'remove'], orphanRemoval: true)]
private ?ArtistImage $image = null;

public function getImage(): ?ArtistImage
{
    return $this->image;
}

public function setImage(?ArtistImage $image): self
{
    if ($image === null || $image->getImage() === null) {
        $this->image = null;
    } else {
        $this->image = $image;
        $this->image->setArtist($this);
    }
    return $this;
}
```

> **Note:** `orphanRemoval: true` ensures the image entity is deleted when the image is removed from the form.

---

### 3. Add the field to the form type

```php
// src/Form/ArtistType.php
use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use App\Entity\ArtistImage;

$builder->add('image', ImageType::class, [
    'label'      => 'Photo',
    'data_class' => ArtistImage::class,
    'required'   => false,
]);
```

---

### 4. Create the migration

```sql
CREATE TABLE app_artist_image (
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
    artist_id INT DEFAULT NULL,
    INDEX IDX_app_artist_image_image (image_id),
    UNIQUE INDEX UNIQ_app_artist_image_artist (artist_id),
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4;

ALTER TABLE app_artist_image
    ADD CONSTRAINT FK_app_artist_image_image
        FOREIGN KEY (image_id) REFERENCES aropixel_image (id),
    ADD CONSTRAINT FK_app_artist_image_artist
        FOREIGN KEY (artist_id) REFERENCES app_artist (id) ON DELETE CASCADE;
```

> **Tip:** Let Doctrine generate the migration instead of writing it by hand:
> ```bash
> bin/console doctrine:migrations:diff
> ```

---

## Adding crop support

Crops allow the editor to define a specific region of the image for each Liip Imagine filter (e.g., a 16/9 banner crop, a square thumbnail crop).

### 1. Create the crop entity

```php
// src/Entity/ArtistImageCrop.php
namespace App\Entity;

use Aropixel\AdminBundle\Entity\AttachedImageInterface;
use Aropixel\AdminBundle\Entity\Crop;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'app_artist_image_crop')]
class ArtistImageCrop extends Crop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ArtistImage::class, inversedBy: 'crops')]
    private ?ArtistImage $image = null;

    public function getId(): ?int { return $this->id; }

    public function getImage(): AttachedImageInterface { return $this->image; }

    public function setImage(?ArtistImage $image): self
    {
        $this->image = $image;
        return $this;
    }
}
```

### 2. Update the image entity

Add `CroppableInterface`, `CroppableTrait`, and the `$crops` collection:

```php
// src/Entity/ArtistImage.php
use Aropixel\AdminBundle\Entity\AttachedImage;
use Aropixel\AdminBundle\Entity\CroppableInterface;
use Aropixel\AdminBundle\Entity\CroppableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'app_artist_image')]
class ArtistImage extends AttachedImage implements CroppableInterface
{
    use CroppableTrait;

    // ... id and artist fields as before ...

    /** @var Collection<int, ArtistImageCrop>|null */
    #[ORM\OneToMany(targetEntity: ArtistImageCrop::class, mappedBy: 'image', cascade: ['persist', 'remove'])]
    protected ?Collection $crops = null;

    public function addCrop(ArtistImageCrop $crop): self
    {
        if (!$this->getCrops()->contains($crop)) {
            $this->crops[] = $crop;
            $crop->setImage($this);
        }
        return $this;
    }

    public function removeCrop(ArtistImageCrop $crop): self
    {
        if ($this->getCrops()->removeElement($crop) && $crop->getImage() === $this) {
            $crop->setImage(null);
        }
        return $this;
    }
}
```

`CroppableTrait` provides `getCrops()`, `getImageUid()`, and `getCropsInfos()`.

### 3. Configure crops in the form

```php
$builder->add('image', ImageType::class, [
    'data_class' => ArtistImage::class,
    'crop_class' => ArtistImageCrop::class,
    'crops' => [
        'artist_portrait'  => 'Portrait (3/4)',
        'artist_thumbnail' => 'Thumbnail (1/1)',
    ],
]);
```

The keys in `crops` must match Liip Imagine filter names defined in `config/packages/liip_imagine.yaml`. Filters without a `thumbnail` transformation are silently ignored by the crop tool.

### 4. Add the crop table to the migration

```sql
CREATE TABLE app_artist_image_crop (
    id INT AUTO_INCREMENT NOT NULL,
    filter VARCHAR(255) NOT NULL,
    crop VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL,
    image_id INT DEFAULT NULL,
    INDEX IDX_app_artist_image_crop_image (image_id),
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4;

ALTER TABLE app_artist_image_crop
    ADD CONSTRAINT FK_app_artist_image_crop_image
    FOREIGN KEY (image_id) REFERENCES app_artist_image (id) ON DELETE CASCADE;
```

---

## Filename mode

In filename mode, no dedicated image entity is needed. The image filename is stored directly as a string field on the parent entity.

```php
// src/Entity/Article.php
#[ORM\Column(nullable: true)]
private ?string $coverFilename = null;
```

```php
// src/Form/ArticleType.php
$builder->add('cover', ImageType::class, [
    'data_value'  => 'coverFilename',  // property that stores the filename
    'crops_value' => 'coverCrops',     // property that stores crops as a JSON array (optional)
    'crops' => [
        'article_cover' => 'Cover (16/9)',
    ],
]);
```

**Trade-offs:**

| | Entity mode | Filename mode |
|---|---|---|
| Separate DB table | Yes | No |
| Shared media library | Yes | Yes |
| Per-image alt text | Yes | No |
| Crop support | Full (dedicated entity) | Partial (stored as JSON in parent) |
| Recommended for | Most cases | Simple cases, rapid prototyping |

---

## ImageType options reference

| Option | Type | Default | Description |
|---|---|---|---|
| `data_class` | `string` | `null` | FQCN of the image entity (extends `AttachedImage`). Required in entity mode. |
| `data_value` | `string` | `null` | Property name that stores the filename on the parent entity. Required in filename mode. |
| `crop_class` | `string` | `null` | FQCN of the crop entity (extends `Crop`). Used in entity mode with crops. |
| `crops` | `array` | `null` | Map of Liip filter slug → human label. Enables the crop tool. |
| `crops_value` | `string` | `'crops'` | *(Filename mode only)* Property that stores crop coordinates as JSON on the parent entity. |
| `library` | `string` | `null` | FQCN used to filter the media library. Defaults to the image entity class. |
| `required` | `bool` | `false` | Whether the image field is required. |
| `description` | `string` | `null` | Helper text displayed below the widget. |
| `card_footer` | `bool` | `true` | Whether to display the widget footer (upload/library buttons). |
| `accept` | `string` | `null` | MIME types accepted by the upload input (e.g., `'image/png,image/jpeg'`). |
| `max_size` | `int` | `null` | Maximum upload size in bytes. |
| `grid` | `string` | `null` | Bootstrap grid class applied to the widget container. |

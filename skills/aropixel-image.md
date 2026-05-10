---
name: aropixel-image
description: >
  Attach a single image to a Symfony entity using AropixelAdminBundle's ImageType.
  Use this skill whenever the user asks to add a photo, a cover image, a thumbnail,
  an avatar, or any single image field to an entity.
  Covers the full workflow: generating with aropixel:make:image, manual entity setup,
  form integration with ImageType, migration, and optional crop support.
  Also use when the user asks to add alt text, configure the media library for an image,
  or restrict upload MIME types / file size on a single image field.
---

# Skill: Single Image with AropixelAdminBundle

## Quick start — generator command

The fastest path is the generator. It creates the image entity (and optionally the crop entity) and updates the parent entity automatically:

```bash
bin/console aropixel:make:image
```

Interactive prompts:
1. **Parent entity class** — e.g. `App\Entity\Artist`
2. **Image property name** — e.g. `image` → generates `ArtistImage`
3. **Croppable?** — also generate `ArtistImageCrop`
4. **Auto-update parent entity?** — inserts property and methods directly

Non-interactive:
```bash
bin/console aropixel:make:image \
  --parent-class="App\Entity\Artist" \
  --property=image \
  --croppable \
  --auto-update
```

After running, two steps remain: [add the field to the form](#3--add-to-the-form-type) and [create the migration](#4--create-the-migration).

---

## Manual setup (entity mode)

### 1 — Create the image entity

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
    public function setArtist(?Artist $artist): self { $this->artist = $artist; return $this; }
}
```

`AttachedImage` already provides: `image` (FK), `position`, `title`, `link`, `description`, `attrTitle`, `attrAlt`, `attrClass`, timestamps.

> **Invariant:** `AttachedImage::setImage()` saves `$oldImage` internally — never call `setOldImage()` manually.

---

### 2 — Update the parent entity

```php
// src/Entity/Artist.php  — add inside the class

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

> `orphanRemoval: true` deletes the `ArtistImage` row when the image is removed from the form.

---

### 3 — Add to the form type

```php
use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use App\Entity\ArtistImage;

$builder->add('image', ImageType::class, [
    'label'      => 'Photo',
    'data_class' => ArtistImage::class,
    'required'   => false,
]);
```

---

### 4 — Create the migration

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
    ADD CONSTRAINT FK_app_artist_image_image  FOREIGN KEY (image_id)  REFERENCES aropixel_image (id),
    ADD CONSTRAINT FK_app_artist_image_artist FOREIGN KEY (artist_id) REFERENCES app_artist (id) ON DELETE CASCADE;
```

Or let Doctrine generate it: `bin/console doctrine:migrations:diff`

---

## Adding crop support

### 1 — Create the crop entity

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
    public function setImage(?ArtistImage $image): self { $this->image = $image; return $this; }
}
```

### 2 — Update the image entity

```php
use Aropixel\AdminBundle\Entity\CroppableInterface;
use Aropixel\AdminBundle\Entity\CroppableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ArtistImage extends AttachedImage implements CroppableInterface
{
    use CroppableTrait;

    // ... id and artist fields unchanged ...

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

`CroppableTrait` provides `getCrops()`, `getImageUid()`, and `getCropsInfos()` — do not implement them manually.

### 3 — Configure crops in the form

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

Keys in `crops` must match Liip Imagine filter names defined in `config/packages/liip_imagine.yaml`. Filters without a `thumbnail` transformation are silently ignored.

### 4 — Add the crop table

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

## ImageType options reference

| Option | Default | Description |
|---|---|---|
| `data_class` | `null` | FQCN of the image entity (extends `AttachedImage`). Required in entity mode. |
| `data_value` | `null` | Property storing the filename on the parent. Required in filename mode. |
| `crop_class` | `null` | FQCN of the crop entity. Used in entity mode with crops. |
| `crops` | `null` | Map of Liip filter slug → label. Enables the crop tool. |
| `crops_value` | `'crops'` | *(Filename mode)* Property that stores crop coordinates as JSON. |
| `library` | `null` | FQCN used to filter the media library. Defaults to the image entity class. |
| `required` | `false` | Whether the image field is required. |
| `description` | `null` | Helper text displayed below the widget. |
| `card_footer` | `true` | Whether to display the widget footer (upload/library buttons). |
| `accept` | `null` | MIME types accepted by the upload input (e.g. `'image/png,image/jpeg'`). |
| `max_size` | `null` | Maximum upload size in bytes. |

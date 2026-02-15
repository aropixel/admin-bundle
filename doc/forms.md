# Form Custom Types Documentation

AropixelAdminBundle provides several custom Symfony Form Types to simplify the creation of advanced administration interfaces. These types are pre-configured to work with the bundle's layout and assets.

## Summary

- [Media Types](#media-types)
    - [ImageType](#imagetype)
    - [GalleryType (Images)](#gallerytype-images)
    - [FileType](#filetype)
    - [GalleryType (Files)](#gallerytype-files)
- [Technical Types](#technical-types)
    - [Select2Type](#select2type)
    - [EntityHiddenType](#entityhiddentype)
    - [CollectionHiddenType](#collectionhiddentype)
    - [TranslatableType](#translatabletype)
- [UI Types](#ui-types)
    - [ColorType](#colortype)
    - [ToggleSwitchType](#toggleswitchtype)
    - [VideoType](#videotype)

---

## Media Types

### ImageType

Used to display an image widget with an upload tool, a media library, and a cropping tool.

**Twig block:** `aropixel_admin_image_widget`

**Options:**
- `data_class`: The class of the entity storing the image (required in entity mode).
- `data_value`: The property name storing the filename (required in filename mode).
- `crop_class`: The class that stores crop information.
- `crops`: An array of available crops (e.g., `['main' => 'Main crop']`).
- `library`: The entity name for filtering the media library.

**Usage:**
```php
$builder->add('image', ImageType::class, [
    'label' => 'Profile Picture',
    'data_class' => UserImage::class,
    'crops' => ['avatar' => 'Avatar'],
]);
```

### GalleryType (Images)

Manages a collection of images with a library modal and sorting capabilities.

**Twig block:** `aropixel_admin_gallery_widget`

**Options:**
- `image_class`: The entity class for the images (required).
- `fields`: Enabled additional fields for each image (`title`, `description`, `link`).
- `crops`: Array of available crops for the gallery images.

**Usage:**
```php
$builder->add('gallery', GalleryType::class, [
    'image_class' => ProductImage::class,
    'fields' => ['title' => true],
    'crops' => ['gallery' => 'Gallery Crop'],
]);
```

### FileType

Displays a single file widget with a library modal.

**Twig block:** `aropixel_admin_file_row`

**Options:**
- `data_class`: The entity class storing the file association.

**Usage:**
```php
$builder->add('document', FileType::class, [
    'label' => 'Manual',
    'data_class' => ProductFile::class,
]);
```

### GalleryType (Files)

Manages a collection of files.

**Twig block:** `aropixel_admin_gallery_files_row`

**Usage:**
```php
$builder->add('files', GalleryType::class, [
    'label' => 'Documents',
    'entry_type' => GalleryFileType::class,
]);
```

---

## Technical Types

### Select2Type

Provides a Select2 input with AJAX support for entity selection.

**Twig block:** `aropixel_admin_select2_row`

**Options:**
- `repository`: The entity class (required).
- `route`: The AJAX route name to fetch results (required).
- `choice_label`: The property name to display as label.
- `multiple`: Whether to allow multiple selection.

**Usage:**
```php
$builder->add('category', Select2Type::class, [
    'repository' => Category::class,
    'route' => 'admin_category_ajax_search',
    'choice_label' => 'title',
]);
```

### EntityHiddenType

Stores an entity ID in a hidden field. Useful for associating entities via JavaScript.

**Twig block:** `hidden_widget`

**Options:**
- `class`: The entity class name (required).

**Usage:**
```php
$builder->add('category', EntityHiddenType::class, [
    'class' => Category::class,
]);
```

### CollectionHiddenType

Stores a collection of entities in a hidden select field.

**Twig block:** `aropixel_admin_collection_hidden_row`

**Options:**
- `repository`: The entity class (required).
- `multiple`: true.

**Usage:**
```php
$builder->add('tags', CollectionHiddenType::class, [
    'repository' => Tag::class,
    'multiple' => true,
]);
```

### TranslatableType

Handles multi-language fields (Gedmo Personal Translations). Generates one input per configured locale.

**Twig block:** `aropixel_admin_translatable_row`

**Options:**
- `personal_translation`: The class name of the translation entity (required).
- `field`: The name of the translated property.
- `widget`: The underlying form type to use (default: `TextType`).

**Usage:**
```php
$builder->add('title', TranslatableType::class, [
    'personal_translation' => ProductTranslation::class,
    'widget' => TextType::class,
]);
```

---

## UI Types

### ColorType

A simple color picker input.

**Twig block:** `aropixel_admin_color_widget`

**Options:**
- `format`: The color format (hex, rgb, etc. Default: `hex`).

**Usage:**
```php
$builder->add('mainColor', ColorType::class, [
    'format' => 'hex',
]);
```

### ToggleSwitchType

A Bootstrap-style toggle switch (checkbox).

**Twig block:** `aropixel_admin_toggle_switch_row`

**Usage:**
```php
$builder->add('enabled', ToggleSwitchType::class, [
    'label' => 'Active',
]);
```

### VideoType

Input for video embed code with a preview.

**Twig block:** `aropixel_admin_video_row`

**Usage:**
```php
$builder->add('videoEmbed', VideoType::class, [
    'label' => 'YouTube Embed Code',
]);
```

# Form Custom Types Documentation

AropixelAdminBundle provides several custom Symfony Form Types to simplify the creation of advanced administration interfaces. These types are pre-configured to work with the bundle's layout and assets.

## Summary

- [Media Types](#media-types)
    - [ImageType](#imagetype)
    - [GalleryType (Images)](#gallerytype-images)
    - [FileType](#filetype)
    - [GalleryType (Files)](#gallerytype-files)
- [Technical Types](#technical-types)
    - [DateTimeType](#datetimetype)
    - [DateType](#datetype)
    - [TimeType](#timetype)
    - [Select2Type](#select2type)
    - [FilterableEntityType](#filterableentitytype)
    - [FilterableEntitiesType](#filterableentitiestype)
    - [EntityHiddenType](#entityhiddentype)
    - [CollectionHiddenType](#collectionhiddentype)
    - [CollectionType](#collectiontype)
    - [TranslatableType](#translatabletype)
    - [SyliusTranslatableType](#syliustranslatabletype)
- [UI Types](#ui-types)
    - [EditorType (QuillJS)](#editortype-quilljs)
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
- `accept`: The file types to accept in the upload tool (e.g., `'image/png,image/jpeg'`).
- `max_size`: The maximum file size allowed for upload in bytes (e.g., `2 * 1024 * 1024` for 2MB).

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
- `accept`: The file types to accept in the upload tool (e.g., `'image/png,image/jpeg'`).
- `max_size`: The maximum file size allowed for upload in bytes.

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

**Twig function:** `aropixel_file_url(file)`
Use this function to generate the public download URL for a file.

> **Note:** For the download link to be publicly accessible, ensure the `download.yaml` routes are imported without a protected prefix (see [Installation](installation.md#include-the-routes)).

**Options:**
- `data_class`: The entity class storing the file association.
- `accept`: The file types to accept in the upload tool (e.g., `'application/pdf'`).
- `max_size`: The maximum file size allowed for upload in bytes.

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

**Twig function:** `aropixel_file_url(file)`
Use this function to generate the public download URL for each file in the gallery.

**Options:**
- `accept`: The file types to accept in the upload tool (e.g., `'application/pdf,application/msword'`).
- `max_size`: The maximum file size allowed for upload in bytes.

**Usage:**
```php
$builder->add('files', GalleryType::class, [
    'label' => 'Documents',
    'entry_type' => GalleryFileType::class,
]);
```

---

## Technical Types

### DateTimeType

An extension of the standard Symfony `DateTimeType` pre-configured to work with the bundle's date and time pickers. It sets the following options by default:
- `date_widget`: `single_text`
- `time_widget`: `single_text`
- `date_format`: `yyyy-MM-dd`

**Twig block:** `aropixel_admin_datetime_widget`

**Usage:**
```php
$builder->add('publishAt', DateTimeType::class, [
    'label' => 'Publish at',
    'required' => false,
]);
```

### DateType

An extension of the standard Symfony `DateType` pre-configured to work with the bundle's date picker. It sets the following options by default:
- `widget`: `single_text`
- `format`: `yyyy-MM-dd`

**Twig block:** `aropixel_admin_date_widget`

**Usage:**
```php
$builder->add('publishedAt', DateType::class, [
    'label' => 'Published at',
    'required' => false,
]);
```

### TimeType

An extension of the standard Symfony `TimeType` pre-configured to work with the bundle's time picker. It sets the following option by default:
- `widget`: `single_text`

**Twig block:** `aropixel_admin_time_widget`

**Usage:**
```php
$builder->add('startAt', TimeType::class, [
    'label' => 'Starts at',
]);
```

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

### FilterableEntityType

An extension of `Select2Type` for single entity selection with AJAX search. It sets `multiple` to `false` and `required` to `false` by default.

**Twig block:** `aropixel_admin_select2_row` (inherited from `Select2Type`)

**Options:**
- `repository`: The entity class (required).
- `route`: The AJAX route name to fetch results (required).
- `choice_label`: The property name to display as label (default: 'label').

**Usage:**
```php
$builder->add('author', FilterableEntityType::class, [
    'label'      => 'Author',
    'repository' => Author::class,
    'route'      => 'admin_author_ajax',
]);
```

### FilterableEntitiesType

An extension of `FilterableEntityType` for multiple entity selection (ManyToMany/OneToMany). It sets `multiple` to `true` by default.

**Twig block:** `aropixel_admin_select2_row` (inherited from `Select2Type`)

**Options:**
- `repository`: The entity class (required).
- `route`: The AJAX route name to fetch results (required).
- `choice_label`: The property name to display as label (default: 'label').

**Usage:**
```php
$builder->add('similarAlbums', FilterableEntitiesType::class, [
    'label'      => 'Similar Albums',
    'repository' => Album::class,
    'route'      => 'admin_album_select2',
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

### CollectionType

Handles a collection of forms with a table view and a centralized Bootstrap offcanvas for editing each item.

**Twig block:** `aropixel_admin_collection_widget`

**Options:**
- `columns`: (array) Associative array of `label => config` to display in the table.
    - If `config` is a string, it represents the `field_path`.
    - If `config` is an array, it can contain:
        - `field`: (string) The path to the field (e.g., `track.name`).
        - `display`: (string) Set to `'label'` to display the field value as a live-updating text instead of a form widget.
        - `render`: (closure) A function for custom HTML rendering: `function($field, $item) { return '...'; }`.
- `button_add_label`: (string) Label for the add button (default: "Ajouter un élément").
- `modal_title`: (string) Title for the edit offcanvas (default: "Détails de l'élément").
- `sortable`: (boolean) Enable drag-and-drop sorting (default: `true`).

**Basic Usage:**
```php
$builder->add('variants', CollectionType::class, [
    'entry_type' => VariantType::class,
    'columns' => [
        'Nom' => [
            'field' => 'name',
            'display' => 'label', // Displays as text and updates live when edited in offcanvas
        ],
        'Prix' => 'price', // Displays the default form widget
    ],
    'button_add_label' => 'Ajouter une variante',
    'modal_title' => 'Édition de la variante',
]);
```

**Advanced Usage (Nested Fields & Custom Rendering):**
You can use dot notation to access fields in nested form types and provide custom HTML rendering.

```php
$builder->add('tracklists', CollectionType::class, [
    'entry_type' => TracklistType::class,
    'columns' => [
        'Pos.' => 'position',
        'Titre' => [
            'field' => 'track.name',
            'display' => 'label',
        ],
        'Status' => [
            'field' => 'track.status',
            'render' => function($field, $item) {
                 return $field->vars['value'] == 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
            },
        ],
    ],
]);
```

**Custom Rendering:**
The `render` closure receives the specific `field` object (FormView) and the whole `item` form object (FormView).

**JavaScript Integration:**
The system uses a centralized Offcanvas (on the right side of the screen) to edit each item. When an item is opened for editing, all its form fields are physically moved into the Offcanvas. When the Offcanvas is closed, the fields are moved back to their original position in the table.

If a column uses `'display' => 'label'`, the text in the table updates automatically in real-time as the user types in the Offcanvas.

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

### SyliusTranslatableType

Handles multi-language fields for entities implementing Sylius Translatable interface. It manages a collection of translations and is compatible with the Sylius Resource Bundle.

**Twig block:** `aropixel_admin_translatable_row` (Uses the same block as `TranslatableType`)

**Options:**
- `field`: The name of the translated property (defaults to the form field name).
- `widget`: The underlying form type to use for each translation (default: `TextType`).
- `locales`: Array of locales to display (default: configured in `aropixel_admin.locales`).
- `required_locale`: Array of locales that are mandatory (default: `kernel.default_locale`).
- `remove_empty`: Whether to remove empty translations from the collection on submit (default: `true`).
- `personal_translation`: Whether the entity uses personal translations (default: `false`).

**Usage:**
```php
$builder->add('name', SyliusTranslatableType::class, [
    'label' => 'Product Name',
    'widget' => TextType::class,
]);
```

---

## UI Types

### EditorType (QuillJS)

A WYSIWYG editor using QuillJS.

**Twig block:** `aropixel_editor_widget`

**Options:**
- `toolbar`: The toolbar configuration. Can be a string (`'full'`, `'simple'`, or a custom type name) or a JSON array of toolbar options. (Default: `'full'`).

### ImageManager Integration

The `EditorType` is automatically integrated with the `ImageManager`. When the 'image' button is clicked in the Quill toolbar, the media library modal opens, allowing you to select or upload images and insert them directly into the editor.

To ensure this works correctly, the `EditorType` automatically handles the necessary metadata (`data-class` and `data-attach-path`) based on the form context.

**Basic Usage:**
```php
$builder->add('content', EditorType::class, [
    'toolbar' => 'simple',
]);
```

**Advanced Usage (Custom configuration):**

You can pass a custom toolbar array directly from PHP:

```php
$builder->add('content', EditorType::class, [
    'toolbar' => [
        ['bold', 'italic'],
        ['link', 'image']
    ],
]);
```

Or you can register a named configuration in your JavaScript:

```javascript
window.aropixelQuillToolbars = {
    'my_custom_toolbar': [
        [{ 'header': [1, 2, false] }],
        ['bold', 'italic'],
        [{ 'color': [] }]
    ]
};
```

And use it in your FormType:

```php
$builder->add('content', EditorType::class, [
    'toolbar' => 'my_custom_toolbar',
]);
```

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

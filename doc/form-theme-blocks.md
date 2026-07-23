# Form theme — block reference

Every block defined by the form theme applied by `@AropixelAdmin/Form/base.html.twig`.

The whole theme lives in `@AropixelAdmin/Form/layout/`.
`layout/theme.html.twig` is an **assembly file** — nothing but `{% use %}` statements — and
each block lives in a domain sub-theme next to it (`_core`, `_collection`, `_controls`,
`_date`, `_editor`, `_file`, `_gallery`, `_image`, `_select2`, `_translatable`, plus
`_collection_macros`). The `File` column below names the sub-theme that owns each block.

This is the map that must be consulted before touching the theme. A mistake is invisible: a
Twig block that no longer matches anything is silently ignored, so an override simply stops
applying — no error, no deprecation, nothing to notice.

> **Invariant — only `_core` may `{% use %}` the Bootstrap layout**, and it is therefore the
> only sub-theme where `parent()` may be called. A second sub-theme using the Bootstrap
> layout would re-inject Bootstrap's own blocks over `_core`'s overrides, silently (Twig
> merges without warning, last `use` wins). This is why `datetime_widget` sits in `_core`
> and not in `_date` — it calls `parent()`. See the header of `_core.html.twig`.

## The two kinds

**Symfony overrides** — `form_row`, `form_label`, `collection_widget`, `money_widget` and
the rest. These have the widest possible blast radius: they apply to **every form in the
application**, including forms in projects that consume the bundle. Review them, never
remove them casually.

> The trap worth naming: `collection_widget` and the `collection_item` macro look like a
> legacy implementation superseded by `aropixel_admin_collection_widget` and its offcanvas.
> They are not. `collection_widget` is Symfony's standard block for `CollectionType`, so it
> renders every plain Symfony collection an integrator writes. Removing it would break
> those forms silently.

**Bundle types** — the `aropixel_*` blocks. Each one backs a form type registered by the
bundle. The block name is the type's `getBlockPrefix()` plus a suffix (`_widget`, `_row`,
`_entry_row`).

## Audit result

Cross-referencing the 43 blocks against the 24 `getBlockPrefix()` values declared across
all four bundles:

- **No orphan blocks.** Every `aropixel_*` block maps to a registered type. There is no
  dead block to delete — the Bootstrap 3/4 fossil markup that once lived *inside* the blocks
  has been removed (design-system.md §8).
- **Seven types render without a dedicated block, all intentionally:**
  `aropixel_admin_date`, `aropixel_admin_datetime` and `aropixel_admin_time` inherit the
  Symfony date/time types, whose generic `date_widget` / `time_widget` / `datetime_widget`
  blocks the theme already overrides; `aropixel_admin_entity_hidden` extends `HiddenType`
  and needs no styling; `aropixel_admin_slideshow` extends `CollectionType`; and
  `aropixel_admin_library_file` / `aropixel_admin_library_image` are compound types whose
  children are styled individually.

## Blocks

| File | Block | Kind | Backing type |
|---|---|---|---|
| `_core` | `form_label_class` | **Symfony override** | applies to every form |
| `_core` | `form_group_class` | **Symfony override** | applies to every form |
| `_core` | `form_start` | **Symfony override** | applies to every form |
| `_core` | `form_end` | **Symfony override** | applies to every form |
| `_core` | `form_label` | **Symfony override** | applies to every form |
| `_core` | `form_row` | **Symfony override** | applies to every form |
| `_core` | `fieldset_form_row` | **Symfony override** | applies to every form |
| `_core` | `form_errors` | **Symfony override** | applies to every form |
| `_core` | `submit_row` | **Symfony override** | applies to every form |
| `_core` | `textarea_widget` | **Symfony override** | applies to every form |
| `_core` | `choice_widget_expanded` | **Symfony override** | applies to every form |
| `_core` | `datetime_widget` | **Symfony override** | applies to every form (calls `parent()`) |
| `_core` | `widget_attributes` | **Symfony override** | applies to every form |
| `_collection` | `collection_widget` | **Symfony override** | applies to every form |
| `_collection` | `aropixel_admin_collection_widget` | bundle type | `CollectionType` |
| `_collection` | `aropixel_admin_collection_entry_row` | bundle type | `CollectionType` |
| `_collection` | `aropixel_admin_collection_hidden_row` | bundle type | `CollectionHiddenType` |
| `_collection` | `aropixel_admin_collection_hidden_widget` | bundle type | `CollectionHiddenType` |
| `_collection_macros` | `collection_item` | macro | — |
| `_controls` | `aropixel_admin_color_widget` | bundle type | `ColorType` |
| `_controls` | `aropixel_admin_toggle_switch_row` | bundle type | `ToggleSwitchType` |
| `_controls` | `aropixel_admin_video_row` | bundle type | `VideoType` |
| `_controls` | `money_widget` | **Symfony override** | applies to every form |
| `_date` | `date_widget` | **Symfony override** | applies to every form |
| `_date` | `time_widget` | **Symfony override** | applies to every form |
| `_editor` | `aropixel_admin_editor_widget` | bundle type | `EditorType` |
| `_file` | `aropixel_admin_gallery_files_row` | bundle type | `GalleryType` |
| `_file` | `aropixel_admin_gallery_files_widget` | bundle type | `GalleryType` |
| `_file` | `aropixel_admin_gallery_file_row` | bundle type | `GalleryFileType` |
| `_file` | `aropixel_admin_file_row` | bundle type | `FileType` |
| `_gallery` | `aropixel_admin_gallery_widget` | bundle type | `GalleryType` |
| `_gallery` | `aropixel_admin_gallery_row` | bundle type | `GalleryType` |
| `_gallery` | `aropixel_admin_gallery_image_row` | bundle type | `GalleryImageType` |
| `_gallery` | `aropixel_admin_gallery_crops_widget` | bundle type | `GalleryCropsType` |
| `_gallery` | `aropixel_admin_gallery_crops_row` | bundle type | `GalleryCropsType` |
| `_image` | `aropixel_admin_image_widget` | bundle type | `ImageType` |
| `_image` | `aropixel_admin_crops_row` | bundle type | `CropsType` |
| `_image` | `aropixel_admin_crops_widget` | bundle type | `CropsType` |
| `_image` | `aropixel_admin_crop_row` | bundle type | `CropType` |
| `_select2` | `choice_widget_collapsed` | **Symfony override** | applies to every form |
| `_select2` | `aropixel_admin_select2_row` | bundle type | `Select2Type` |
| `_select2` | `aropixel_admin_select2_widget` | bundle type | `Select2Type` |
| `_translatable` | `aropixel_admin_translatable_row` | bundle type | `TranslatableType` |
| `_translatable` | `aropixel_admin_translatable_widget` | bundle type | `TranslatableType` |

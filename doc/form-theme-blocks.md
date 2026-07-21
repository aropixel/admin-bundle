# Form theme — block reference

Every block defined by `@AropixelAdmin/Form/layout.html.twig`, the form theme applied by
`@AropixelAdmin/Form/base.html.twig`.

This is the map that must be consulted before touching the theme. It exists because the
file is 867 lines long, mixes two very different kinds of block, and a mistake in one of
them is invisible: a Twig block that no longer matches anything is silently ignored, so an
override simply stops applying — no error, no deprecation, nothing to notice.

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
  dead block to delete — the dead weight in this file is the Bootstrap 3/4 markup *inside*
  the blocks, not the blocks themselves.
- **Seven types render without a dedicated block, all intentionally:**
  `aropixel_admin_date`, `aropixel_admin_datetime` and `aropixel_admin_time` inherit the
  Symfony date/time types, whose generic `date_widget` / `time_widget` / `datetime_widget`
  blocks the theme already overrides; `aropixel_admin_entity_hidden` extends `HiddenType`
  and needs no styling; `aropixel_admin_slideshow` extends `CollectionType`; and
  `aropixel_admin_library_file` / `aropixel_admin_library_image` are compound types whose
  children are styled individually.

## Blocks

| Line | Block | Kind | Backing type |
|---|---|---|---|
| 6 | `form_label_class` | **Symfony override** | applies to every form |
| 7 | `form_group_class` | **Symfony override** | applies to every form |
| 10 | `form_start` | **Symfony override** | applies to every form |
| 16 | `form_end` | **Symfony override** | applies to every form |
| 28 | `form_label` | **Symfony override** | applies to every form |
| 81 | `form_row` | **Symfony override** | applies to every form |
| 129 | `fieldset_form_row` | **Symfony override** | applies to every form |
| 147 | `form_errors` | **Symfony override** | applies to every form |
| 159 | `submit_row` | **Symfony override** | applies to every form |
| 163 | `collection_widget` | **Symfony override** | applies to every form |
| 192 | `aropixel_admin_collection_widget` | bundle type | `CollectionType` |
| 235 | `aropixel_admin_collection_entry_row` | bundle type | `CollectionType` |
| 305 | `collection_item` | macro | — |
| 333 | `aropixel_admin_color_widget` | bundle type | `ColorType` |
| 344 | `aropixel_admin_toggle_switch_row` | bundle type | `ToggleSwitchType` |
| 356 | `aropixel_admin_video_row` | bundle type | `VideoType` |
| 378 | `textarea_widget` | **Symfony override** | applies to every form |
| 384 | `aropixel_editor_widget` | bundle type | `EditorType` |
| 408 | `aropixel_admin_gallery_widget` | bundle type | `GalleryType` |
| 440 | `aropixel_admin_gallery_row` | bundle type | `GalleryType` |
| 445 | `aropixel_admin_gallery_image_row` | bundle type | `GalleryImageType` |
| 460 | `aropixel_admin_gallery_crops_widget` | bundle type | `GalleryCropsType` |
| 469 | `aropixel_admin_gallery_files_row` | bundle type | `GalleryType` |
| 491 | `aropixel_admin_gallery_files_widget` | bundle type | `GalleryType` |
| 529 | `aropixel_admin_gallery_file_row` | bundle type | `GalleryFileType` |
| 538 | `aropixel_admin_file_row` | bundle type | `FileType` |
| 607 | `aropixel_admin_image_widget` | bundle type | `ImageType` |
| 632 | `aropixel_admin_gallery_crops_row` | bundle type | `GalleryCropsType` |
| 638 | `aropixel_admin_crops_row` | bundle type | `CropsType` |
| 646 | `aropixel_admin_crops_widget` | bundle type | `CropsType` |
| 656 | `aropixel_admin_crop_row` | bundle type | `CropType` |
| 662 | `choice_widget_collapsed` | **Symfony override** | applies to every form |
| 693 | `choice_widget_expanded` | **Symfony override** | applies to every form |
| 717 | `datetime_widget` | **Symfony override** | applies to every form |
| 723 | `date_widget` | **Symfony override** | applies to every form |
| 746 | `time_widget` | **Symfony override** | applies to every form |
| 766 | `aropixel_admin_select2_row` | bundle type | `Select2Type` |
| 777 | `aropixel_admin_select2_widget` | bundle type | `Select2Type` |
| 787 | `aropixel_admin_collection_hidden_row` | bundle type | `CollectionHiddenType` |
| 796 | `aropixel_admin_collection_hidden_widget` | bundle type | `CollectionHiddenType` |
| 807 | `money_widget` | **Symfony override** | applies to every form |
| 823 | `widget_attributes` | **Symfony override** | applies to every form |
| 841 | `aropixel_admin_translatable_row` | bundle type | `TranslatableType` |
| 856 | `aropixel_admin_translatable_widget` | bundle type | `TranslatableType` |

# Upgrading to v3

v3 is a deliberate major release. The admin UI was rebuilt on a design-token system, moved to
Bootstrap 5, and swapped its icon font and rich-text editor. Breaking changes were accepted
where the gain justified them — this guide is the map.

Changes fall in two groups, and the second matters more:

- **Visible breaks** fix themselves loudly — a renamed CSS class leaves the page visibly wrong,
  a changed template breaks the layout, you see it and you fix it.
- **Silent breaks** produce *no error*. An override simply stops applying. These can survive
  for months in production unnoticed, so **read the "Silent breaks" section carefully even if
  everything looks fine.**

---

## Icons — the largest surface

FontAwesome, Ionicons and flag-icon-css are **all removed** (~26 MB of assets), replaced by
**[symfony/ux-icons](https://symfony.com/bundles/ux-icons/current/index.html)** with the
**Lucide** set. See **[Icons](icons.md)** for the full system.

In your own templates, replace every icon-font tag:

```diff
- <i class="fas fa-trash"></i>
+ {{ ux_icon('lucide:trash-2') }}

- <i class="fas fa-file-import me-1"></i>
+ {{ ux_icon('lucide:import', {class: 'me-1'}) }}
```

Then, so production makes no network call for icons, run and commit the result of:

```bash
php bin/console ux:icons:lock
```

Two more consequences:

- **Menu-item icons are no longer rendered.** The fullscreen menu shows labels only, so an
  `['icon' => …]` property on a menu `Link`/`SubMenu` now has no visible effect. Remove it, or
  override the menu templates to render it with `ux_icon` — see
  [Admin Menu Customization](admin_menu.md).
- **The language selector no longer uses flags.** A language is not a country; the selector
  now shows the **locale code** (`FR`, `EN`, …). Nothing to do unless you themed the flags.

---

## CSS & styling — now Bootstrap 5

The admin was on a Bootstrap 3/4-era theme (Stisla); it is now **Bootstrap 5**. If you wrote
custom admin templates, expect these:

- **Data attributes** — `data-toggle` / `data-target` / `data-dismiss` → `data-bs-toggle` /
  `data-bs-target` / `data-bs-dismiss`. A stale `data-toggle` **silently** stops opening its
  modal/dropdown (see "Silent breaks").
- **Removed Bootstrap 3/4 markup** — `.panel*` → `.card*`; `.pull-right` / `.pull-left` →
  `.float-end` / `.float-start`; `.btn-block` → `.w-100`; `.dropdown-menu-right` →
  `.dropdown-menu-end`.
- **Maison spacing utilities removed** — the 5px-step helpers (`.m-r-15`, `.m-t-20`,
  `.no-margin`, …) were migrated to Bootstrap (`.me-3`, `.mt-4`, `.m-0`, …) and deleted. Use
  Bootstrap spacing utilities. See the full removed-classes table in
  **[CSS Customization](css_customization.md)**.
- **Stylesheet renamed** — `custom.css` → `style.css`, and it is now an `@import` manifest of
  component files, not a monolith.
- **Legacy CSS variables promoted to tokens** — `--main-*` / `--second-*` are gone; recolour
  the admin through the `--aro-*` tokens instead. See **[Theming](theming.md)**.

The supported way to restyle the admin is now token overrides, not class surgery — start at
[Theming](theming.md) and browse the live catalogue at `/admin/_catalog` (dev only).

---

## Silent breaks — check these even if the app looks fine

No error is raised for any of the following. Nothing turns red; the customization just stops
taking effect.

### Form-theme block overrides

If you overrode a form-theme block by name, verify the name still exists — a Twig block that
matches nothing is ignored silently. The one **renamed** block:

| Old block | New block |
|---|---|
| `aropixel_editor_widget` | `aropixel_admin_editor_widget` |

The backing type's prefix changed with it: `EditorType::getBlockPrefix()` is now
`aropixel_admin_editor` (was `aropixel_editor`). If you referenced the old prefix anywhere,
update it. See the full list of blocks in
**[Form Theme — Block Reference](form-theme-blocks.md)**.

### Form-theme path

The theme moved from `@AropixelAdmin/Form/layout.html.twig` to
**`@AropixelAdmin/Form/layout/theme.html.twig`** (now split into per-domain sub-themes). You
only need to change anything if you applied it directly with `{% form_theme … %}` — extending
`@AropixelAdmin/Form/base.html.twig` (the usual path) is unaffected.

### Modal / dropdown triggers

A `data-toggle="modal"` left over from Bootstrap 4 raises no error — the button just does
nothing. Grep your admin templates for `data-toggle`, `data-target`, `data-dismiss` and add the
`-bs-` (see above).

---

## Rich-text editor — CKEditor → Quill

The WYSIWYG editor is now **QuillJS**, exposed through `EditorType`:

```php
use Aropixel\AdminBundle\Form\Type\EditorType;

$builder->add('content', EditorType::class, ['toolbar' => 'full']);
```

If your forms used a CKEditor-based editor (e.g. a `class: 'ckeditor'` textarea wired to
`CKEDITOR.replace(...)`), switch them to `EditorType`. With CKEditor removed, the old
initialisation throws `CKEDITOR is not defined`.

---

## PageBundle — custom-block icons

If you register **custom blocks** for the page builder, their `icon` is now an **icon name**
for the icon system, not a FontAwesome class. Update your `custom_blocks` config:

```diff
  custom_blocks:
      - type: my_block
        label: My block
-       icon: 'fas fa-star'
+       icon: 'lucide:star'
```

The default is now `lucide:puzzle` (was `fas fa-puzzle-piece`). The builder renders it with
`ux_icon(icon)`, so any [Iconify](https://icon-sets.iconify.design/) name works — see
[Icons](icons.md).

## Database

The sandbox and the bundle's migrations target **MySQL**. See the database note in the project
README / `CLAUDE.md` if you are wiring your own datastore.

---

## Checklist

- [ ] Replace `<i class="fa* fa-…">` with `{{ ux_icon('lucide:…') }}` in your templates.
- [ ] Run `php bin/console ux:icons:lock` and commit `assets/icons/`.
- [ ] Grep for `data-toggle` / `data-target` / `data-dismiss` → add `-bs-`.
- [ ] Grep for `.panel`, `.pull-right`, `.btn-block`, `.m-r-*` / `.m-t-*` (maison spacing) and
      swap for the Bootstrap 5 equivalents.
- [ ] Verify any form-theme block override still matches a real block name (esp.
      `aropixel_admin_editor_widget`).
- [ ] Move rich-text fields to `EditorType`.
- [ ] Recolour through `--aro-*` tokens, not `--main-*` / `--second-*`.

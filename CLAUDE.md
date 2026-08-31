# CLAUDE.md — AdminBundle

> **IMPORTANT — maintenance safeguard**
> This file documents implicit contracts that are not apparent from reading the code.
> **Any change to an invariant listed here must be reflected here immediately.**
> A stale CLAUDE.md is actively misleading — better to delete it than let it lie.

## Documentation

- [Index](doc/index.md)
- [Installation](doc/installation.md)
- [Entities — extension and MappedSuperclass](doc/entities.md)
- [Forms](doc/forms.md)
- [Single image (`ImageType`, `aropixel:make:image`)](doc/image.md)
- [Image gallery (`GalleryType`)](doc/gallery.md)
- [DataTable](doc/datatable.md)
- [CRUD generator (`aropixel:make:crud`)](doc/make_crud.md)
- [User management](doc/create_user.md)
- [i18n](doc/i18n.md)
- [Twig macros](doc/macros.md)
- [Theming — the `--aro-*` token API](doc/theming.md)
- [Icons — ux-icons / Lucide, and overriding](doc/icons.md)
- [Form theme block reference](doc/form-theme-blocks.md)
- [Upgrading to v3](doc/upgrade-v3.md)
- Component catalogue — live at `/admin/_catalog` (dev only)

---

## Component catalogue — keep the static export in sync

The catalogue exists in **two forms**, and they must not drift:

- **Live** — `catalog/index.html.twig` at `/admin/_catalog` (dev only). Rendered on the real
  CSS, it cannot lie about how the admin looks.
- **Static** — `docs/catalog.html` (+ `docs/index.html`), a self-contained export published
  via GitHub Pages, with `doc/assets/catalog-preview.png` as the README hero. It embeds a
  **frozen copy** of the bundle CSS.

> **Whenever a component's CSS changes, rebuild the static export.** Run `castor catalog:build`
> from the repo root (rebuilds the embedded CSS from source and re-shoots the preview;
> `--no-shot` skips the screenshot and needs no running app). Skip this and the published
> catalogue shows the *old* admin — the whole point of the export is defeated. Adding a *new*
> component also means editing the markup of **both** `catalog/index.html.twig` and
> `docs/catalog.html` by hand, then rebuilding.

> **Never hand-edit `docs/catalog.html`'s first `<style>` block** — it is generated. A missing
> `}` there is invisible in the live admin (each `@import`ed file auto-closes at its own EOF)
> but in the single concatenated export it nests every following rule, silently disabling
> whole components. The `qa:css` `brace_imbalance` check catches this at the source.

---

## Non-obvious invariants

### `User` entity (MappedSuperclass)

- `User` is `#[ORM\MappedSuperclass]` — instantiate directly with `new User()`, never via a factory.
- **Never use `UserRepository::create()` in fixtures.** `create()` calls `PasswordInitializer`, overwrites the plain password, and forces `enabled = true` when a plain password is present. In fixtures, use `new User()` + `setPassword($hasher->hashPassword(...))` directly.
- `getRoles()` **always appends `ROLE_ADMIN`** even when the roles array is empty. Passing `ROLE_SUPER_ADMIN` to `setRoles()` alone is not enough — use `setSuperAdmin(true)`.

### `PublishableTrait` + `Publishable`

- The trait provides the logic (`isPublished()`, `isScheduled()`, etc.) but **not the `$status` property** — it must be declared on the concrete class.
- The constants `STATUS_ONLINE` / `STATUS_OFFLINE` live on `Publishable`, not on the trait.
- `isPublished()` uses `property_exists($this, 'publishAt')` — scheduling is only active when these properties exist on the concrete class.

### `AttachedImage` (MappedSuperclass)

- `AttachedImage` is `#[ORM\MappedSuperclass]` — the concrete application class must be `#[ORM\Entity]` and add the inverse relation (`OneToOne` or `ManyToOne` back to the parent entity).
- `setImage()` saves `$oldImage` internally to detect changes — never call `setOldImage()` manually.

### Gedmo (common to all entities)

- **Never call `setSlug()`** — Gedmo generates it on `flush()`. The setter exists but manually overriding it breaks consistency.
- **Never set `createdAt` / `updatedAt`** — Gedmo Timestampable manages them.

### `aropixel:make:crud`

- Backslashes in the FQCN argument are stripped by the Docker shell (`/bin/bash -c`). Always wrap in single quotes: `'App\Entity\Project'`. The command handles this via `extractShortName()`, but the controller name may be wrong if the regex does not match.
- Generated templates are placed in `templates/admin/{entity_snake_case}/` (e.g. `Project` → `admin/project/`).

### `EditorType` (QuillJS)

- The stored HTML and the DOM Quill manipulates are **not the same markup**. Quill 2 puts every list in an `<ol>` with `data-list` on the `<li>`, and injects `<span class="ql-ui">` nodes. `toEditorHtml()` / `toStorageHtml()` in `js/module/quill/quill-editor.js` translate both ways — **never** write `quill.root.innerHTML` straight to the textarea, it leaks the interface nodes into the database.
- `Shift + Enter` relies on a `softbreak` blot registered on tagName `BR`, which overrides the tagName lookup for Quill's own `Break`. Quill still creates its empty-line breaks by blot name, so both coexist — but registering another `BR` blot would break soft line breaks.
- The clean-paste matcher only runs on a real paste, never on load: initial content is assigned through `innerHTML`, which bypasses the clipboard matchers. Loading through `clipboard.convert()` instead would strip colours and sizes from saved content.
- The `file` toolbar handler **must** be passed to the `Quill` constructor. `file` is not a Quill format, so a button whose handler is registered later with `addHandler()` never gets a listener. `image` is a known format and may keep being overridden after construction.
- The `link` format is overridden (`AroLink`) so its value can be `{ href, target }` — the « Nouvel onglet » checkbox in the tooltip drives `target`. The snow tooltip's `save()` is replaced per instance for link mode; the theme's own preview keeps using the *original* `Link.formats()` (string), which is why `AroLink.formats()` returning an object does not break it.
- The `<>` button opens an **editable** source panel that replaces the `.ql-container` (class `quill-source-mode` on the container). The panel's textarea holds *storage* HTML pretty-printed by `prettyHtml()`; `compactHtml()` strips that indentation before the HTML reaches the textarea field or `quill.root.innerHTML` — skipping it would leak literal newlines into the rendered content (`white-space: pre-wrap`).

### `DataTable`

- Columns are defined inside the `index()` action of the controller, not in a constructor.
- `searchIn()` takes Doctrine property names, not SQL column names.
- The response is an extended `Response` — do not re-wrap it in a `JsonResponse`.

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

### `DataTable`

- Columns are defined inside the `index()` action of the controller, not in a constructor.
- `searchIn()` takes Doctrine property names, not SQL column names.
- The response is an extended `Response` — do not re-wrap it in a `JsonResponse`.

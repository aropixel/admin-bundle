# UPGRADE FROM 1.x/2.x TO 3.0

AdminBundle 3.0 is a complete rewrite. There is no in-place upgrade path from
1.x or 2.x: treat it as a new installation and follow
[doc/installation.md](doc/installation.md).

## Requirements

* Symfony 6.4, 7.x or 8.x
* Doctrine ORM 2 or 3 (DoctrineBundle 2.13+ or 3.x)
* `ext-intl`
* Front-end: Stimulus + Turbo through AssetMapper/ImportMap
  (`symfony/stimulus-bundle`), icons through `symfony/ux-icons`.
  Webpack Encore and jQuery-based scripts are no longer used.

## Main changes

* New admin UI, theme and components (alerts, collections, datatables,
  Quill editor, media libraries). See [doc/index.md](doc/index.md).
* CRUD generation with `make:crud`, see [doc/make_crud.md](doc/make_crud.md).
* Multilingual content is supported out of the box, see [doc/i18n.md](doc/i18n.md).
* The companion bundles (BlogBundle, PageBundle, MenuBundle) are released as
  3.0 as well and require `aropixel/admin-bundle ^3.0`.

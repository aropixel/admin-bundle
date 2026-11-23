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

## Silent breaking changes

These keep working without any error but behave differently, so they are worth
checking in templates carried over from 1.x/2.x.

* **DataTables `data-order-column` is now 0-based.** 1.x/2.x subtracted 1 from
  the attribute; 3.0 passes it to DataTables as is, consistent with
  `DataTable::setOrderColumn()`. A table carried over unchanged sorts on the
  column to the right of the intended one by default. Decrement every
  `data-order-column` value (on `.datatable` and `.datatable-ajax` tables).
* **`.datatable-ajax` without `data-src` now loads the page URL itself.** 1.x/2.x
  fell back to `<page URL>/dataTable.json`; 3.0 requests `window.location.pathname`.
  A table relying on the old convention receives the HTML page instead of JSON and
  stays on its loading indicator. Give such tables an explicit `data-src` (or
  `data-ajax-url`) pointing to their JSON route.

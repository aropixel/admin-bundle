# Icons

The admin uses **[symfony/ux-icons](https://symfony.com/bundles/ux-icons/current/index.html)**
with the **[Lucide](https://lucide.dev/icons/)** set — inline SVG, not an icon font. FontAwesome,
Ionicons and flag-icon-css were all removed.

Inline SVG buys three things: the icon inherits the surrounding text colour through
`currentColor` (so it follows the `--aro-*` tokens automatically), it stays crisp at any size,
and there is no font file to load and no flash of unstyled icons.

## Using an icon

```twig
{{ ux_icon('lucide:pencil') }}
```

Every icon is `lucide:<name>`, where `<name>` is the icon's id on
[lucide.dev/icons](https://lucide.dev/icons/) (e.g. `trash-2`, `file-text`, `chevron-right`).

Pass HTML attributes as a second argument — including a `class` for spacing or sizing:

```twig
{{ ux_icon('lucide:trash-2', {class: 'me-2'}) }}
{{ ux_icon('lucide:user', {class: 'me-2', 'aria-hidden': 'true'}) }}
```

By default every icon renders at `1em` × `1em` with `fill: currentColor`, so it matches the
font size and colour of its context. To resize, set a `font-size` (or `width`/`height`) on the
icon or its parent.

## Offline / production — `ux:icons:lock`

By default ux-icons fetches an unknown icon from the Iconify API on first render. That is fine
in development but you do not want a network call in production. Run:

```bash
php bin/console ux:icons:lock
```

It scans every Twig template reachable by the loader — **including the AdminBundle templates in
`vendor/`** — and downloads each icon they use into your application's `assets/icons/`. After
that the admin renders with **zero network dependency**. Add this command to your deployment
process; it is the only icon-related action an integrator needs.

## Overriding an icon

**Local icons take precedence over on-demand ones.** To replace any icon the admin uses —
including a Lucide one — drop your own SVG at the matching path in your application's
`assets/icons/` directory. No configuration:

```
assets/icons/lucide/trash-2.svg      ← overrides lucide:trash-2 everywhere in the admin
```

The path mirrors the icon name: `lucide:trash-2` → `assets/icons/lucide/<name>.svg`, with `:`
becoming a directory separator.

### Your own icons

The same mechanism gives you custom icons under any prefix. An SVG at
`assets/icons/app/logo.svg` is rendered with:

```twig
{{ ux_icon('app:logo') }}
```

Keep the SVG free of a hard-coded `fill`/`stroke` colour (or set it to `currentColor`) so it
inherits the token-driven text colour like the rest of the admin.

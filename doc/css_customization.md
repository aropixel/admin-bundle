# CSS Customization

The admin's styling was rebuilt on a design-token system. This page used to catalogue
utility classes and components by hand; that role now belongs to two better sources:

- **[The component catalogue](../src/Resources/views/catalog/index.html.twig)** — every
  component, in every state, rendered live on the real CSS at **`/admin/_catalog`**
  (dev environment only). It cannot drift from what the admin actually looks like, which a
  hand-written list always eventually does.
- **[Theming](theming.md)** — how to recolour and restyle the admin by overriding
  `--aro-*` tokens. This is the supported customization path.

## Styling your own Twig views

Use the same building blocks the admin uses:

- **Cards** — `.card` with `.card-header` / `.card-body`; `.card-primary` for a teal top
  accent. The primary content surface.
- **Buttons** — `.btn` with `.btn-cta` (coral, one per screen), `.btn-primary` (teal),
  `.btn-secondary` (slate), `.btn-default`, `.btn-outline`, `.btn-danger`. Sizes
  `.btn-small`, `.btn-xs`.
- **Everything else** — see the catalogue.

For spacing and layout, use **Bootstrap 5 utilities** (`mt-3`, `me-2`, `d-flex`, `row` /
`col-*`). The bundle is built on Bootstrap 5.

## Removed in the rebuild

If you are upgrading and referenced these in your own templates, they are gone:

| Removed | Use instead |
|---|---|
| `.ts-xs` … `.ts-5` (font-size scale) | Bootstrap `.fs-*`, or the `--aro-text-*` tokens |
| `.tw-300` … `.tw-700` (font-weight) | Bootstrap `.fw-light` / `.fw-medium` / `.fw-semibold` / `.fw-bold` |
| Most `.m-t-*` / `.p-b-*` (5px-step spacing) | Bootstrap `.mt-*` / `.pb-*` (only a handful of the old ones survive) |
| `.w-20px` … `.w-100px`, `.h-*px` sizing | inline `style` or a Bootstrap sizing utility |
| FontAwesome `<i class="fas fa-…">` | `{{ ux_icon('lucide:…') }}` — see the icons section of the catalogue |

A full old-major → v3 migration guide is a separate document (see the roadmap).

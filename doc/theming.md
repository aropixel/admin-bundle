# Theming

The admin is themed through **CSS custom properties** (design tokens), not by overriding
selectors. Set a handful of `--aro-*` variables and the whole interface follows — buttons,
links, cards, form fields, status indicators.

This is the public, stable theming API. The internal primitives and component selectors are
not; do not target them.

## The quick way — one colour

Most projects only change the brand colour. Point `--aro-color-primary` at your own, after
the bundle's stylesheet loads:

```css
/* your app's stylesheet, loaded after the admin's */
:root {
    --aro-color-primary: #7c3aed;         /* your brand */
    --aro-color-primary-hover: #6d28d9;   /* a slightly darker shade for hover */
}
```

That recolours every primary button, active link, focused field, tab and pagination
control. Because the tokens feed Bootstrap's own variables through a bridge, Bootstrap
components you never touched follow too.

## The theme config

The bundle also exposes three colours through configuration, emitted as tokens at runtime.
Use this when the values come from the database or a per-client config rather than a static
stylesheet:

```yaml
# config/packages/aropixel_admin.yaml
aropixel_admin:
    theme:
        colors:
            btn_background_color: '#7c3aed'   # → --aro-color-primary
            btn_color: '#ffffff'              # → --aro-color-primary-contrast
            background_color: '#7c3aed'       # login side panel
```

These win over the static defaults, so they are the right place for run-time theming.

## The tokens

Override only these — the semantic layer. Values below are the defaults.

### Brand & actions

| Token | Default | Role |
|---|---|---|
| `--aro-color-primary` | `#06BAB4` | Brand teal. Ordinary actions: create, add, apply. |
| `--aro-color-primary-hover` | `#05A29D` | |
| `--aro-color-secondary` | `#2E4F5E` | Slate. Structural / neutral-dark actions. |
| `--aro-color-secondary-hover` | `#223C48` | |
| `--aro-color-cta` | `#FF6B5B` | Coral. **The one high-emphasis action per screen** (save, publish). |
| `--aro-color-cta-hover` | `#F0503F` | |

> The `-contrast` companion of each (`--aro-color-primary-contrast`…) is the text colour
> drawn on top of it — set it too if your brand colour needs dark text.

### Semantic

`--aro-color-success` `#63CEB3` · `--aro-color-danger` `#E52321` ·
`--aro-color-warning` `#F25C05` · `--aro-color-info` `#E39B02`

### Surfaces, text, borders

| Token | Default |
|---|---|
| `--aro-color-bg` | `#F5F8FA` (app background) |
| `--aro-color-surface` | `#FFFFFF` (cards, modals) |
| `--aro-color-text` | `#333333` |
| `--aro-color-text-muted` | `#99A1B7` |
| `--aro-color-border` | `#DDE1E5` |

### Form fields

| Token | Default |
|---|---|
| `--aro-field-bg` | `#FFFFFF` |
| `--aro-field-border` | `#D7DCE2` |

### Status indicators

`--aro-status-online` `#06BAB4` · `--aro-status-scheduled` `#FFC700` ·
`--aro-status-offline` `#E7E7E7`

### Radii, typography

`--aro-radius-input` `5px` · `--aro-radius-card` `6px` · `--aro-radius-pill` `100px` ·
`--aro-font-sans` (Poppins) · `--aro-text-base` `0.875rem` (14px).

Sizes and spacing are in **rem**, so they scale with the reader's browser font-size and
zoom. See the full list in `src/Resources/public/css/foundations/_tokens.css`.

## Seeing it

Every component and every token is rendered live in the **component catalogue**, at
`/admin/_catalog` (dev environment only). It is the fastest way to check what a token change
does before shipping it.

## What not to override

- **Primitives and component internals** — the `--bs-*` bridge variables, the descendant
  selectors inside a component. They change without notice between versions.
- **The bundle's stylesheets themselves.** Add your own after them; the admin's Bootstrap
  is in a cascade layer, so your unlayered rules win without needing `!important`.

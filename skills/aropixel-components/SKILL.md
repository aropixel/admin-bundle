---
name: aropixel-components
description: >
  The AropixelAdminBundle design-system component library — buttons, dialogs, toasts,
  alerts, modals, lists, grids, stats, cards, badges, avatars, form controls and more, all on
  the `--aro-*` tokens. Use this skill whenever you build or style admin UI: choosing a button,
  badge, dialog, toast, alert or dropdown; laying out a list, grid, stat block or card; picking a
  modal size or a presentation; or wondering "is there already a component for this?".
  It routes you to the ready-made component and its canonical markup instead of hand-rolling
  UI that drifts from the design system.
---

# Skill: AropixelAdminBundle design-system components

Before writing any admin markup, **pick from the components below** — they are built on the
`--aro-*` tokens and stay consistent with the rest of the admin. Only if nothing fits do you
*dress* a Bootstrap component; never hand-roll a bespoke widget.

Everything lives in the bundle (in a host project: `vendor/aropixel/admin-bundle/`):

- **CSS** — `src/Resources/public/css/components/_<name>.css`. **Each file's header comment is
  the source of truth: it carries the exact `Markup:` block and the public classes.** Read it
  before using a component.
- **Catalogue (visual reference)** — `docs/catalog.html` (self-contained static export) or the
  dev route `/admin/_catalog`. Every component is shown in all its states.
- **JS** — `src/Resources/public/js/module/…` for the interactive ones (dialog, toast).

> This skill is an **index**, not a copy. For precise markup and every variant, open the
> `_<name>.css` header. When the design system gains a component, that header is updated — this
> index just gains a line.

---

## Golden rules (these are not in the markup — apply them every time)

1. **Reuse, don't reinvent.** Use a component below. No fitting component → dress a Bootstrap
   one through its `--bs-*` variables. Never write bespoke UI CSS in a template.
2. **Tokens only.** Every colour, space, radius and shadow comes from a `--aro-*` token. No raw
   hex, no magic numbers, no `!important` (Bootstrap is in a cascade layer, so our unlayered
   rules already win).
3. **Action hierarchy — the one rule the design exists to state.** Coral `.btn-cta` is the
   **single** key action on a screen (save, publish); teal `.btn-primary` is an ordinary action
   (create, add); slate `.btn-secondary` is structural; `.btn-default` / `.btn-outline` recede
   (cancel, back). **Two corals on a screen and neither means anything.**
4. **Status is a traffic light.** online = green, scheduled = amber, offline = red
   (`--aro-status-*`).

---

## Component index

### Actions
| Component | Use for | Entry point |
|---|---|---|
| Button | Any action | `.btn` + `.btn-cta` / `-primary` / `-secondary` / `-default` / `-outline` / `-danger`; sizes `.btn-small`, `.btn-xs`; `.btn-icon` |
| Button group / segmented | Toolbar, view-switcher | Bootstrap `.btn-group` (selected segment = filled variant) — `_button-group.css` |
| Split button | Primary action + a dropdown of alternatives | `.btn-group` + `.dropdown-toggle-split` — `_split-button.css` |
| Dropdown | A menu of actions/links | `.dropdown` › `.dropdown-menu` › `.dropdown-item` — `_dropdown.css` |
| Row-actions menu (`…`) | Edit / status / delete on a list row | Twig macro `@AropixelAdmin/Macro/actions.html.twig` (delete opens the Dialog) |

### Feedback & overlays
| Component | Use for | Entry point |
|---|---|---|
| **Dialog** | Confirmation (delete, publish…) | JS: `new ConfirmDialog({ intent, size, title, message, confirmLabel, cancelLabel, onConfirm })`. `intent`: danger·warning·success·info·primary. CSS `.aro-dialog` — `dialog/confirm-dialog.js`, `_dialog.css` |
| **Toast** | Floating flash notification | JS: `window.aroToast({ type, title, message })`. `type`: success·danger·warning·info·primary — `toast/toast.js`, `_toast.css` |
| **Alert** | Inline notice in the page flow (form errors, contextual notices) | `.aro-alert` + `--success`/`-danger`/`-warning`/`-info`/`-primary`; slots `__icon`/`__title`/`__text`/`__list`/`__link`/`__actions`/`__close`; modifiers `--accent` (left bar), `--solid` (filled). Dismiss: add `.alert` + `data-bs-dismiss="alert"` — `_alert.css` |
| Modal | A panel / form overlay | Bootstrap `.modal`; size on `.modal-dialog`: `.modal-sm` 400 · default 520 · `.modal-lg` 720 · `.modal-xl` 960 · `.modal-full`. Grey footer is automatic — `_modal.css` |
| Badge & status | A label / a publish state | `.badge`; status dot `.state-icon--online` / `--scheduled` / `--offline`, thumbnail dot `.img-state-icon` — `_badge.css` |

### Layout & data display
| Component | Use for | Entry point |
|---|---|---|
| Avatar | A record's thumbnail (empty state included) | `.aro-avatar` (`--sm` / `--lg`), `__placeholder`, `__status--online` / `--scheduled` — `_avatar.css` |
| Card | A surface / panel | `.card`, `.card-primary` (teal top accent) — `_card.css` |
| Accordion | Collapsible panels (FAQ, settings) | Bootstrap `.accordion` (themed) — `_accordion.css` |
| Stat | KPI figures for dashboards | `.aro-stat` (`.aro-stat--bordered`), `.aro-stat-panel`, `.aro-stat-card`; atoms `__label` / `__value` / `__trend` — `_stat.css` |
| Grid list | Records on a responsive card grid | `.aro-grid-list` + `.aro-grid-card` (centered, action footer) or `.aro-grid-simple` (initials row) — `_grid-list.css` |
| List | Stacked records (lighter than a DataTable) | `.aro-list` (`.aro-list--dark`), `__row` / `__body` / `__name` / `__secondary` / `__meta` / `__chevron` — `_list.css` |
| Section header | Page title + breadcrumb bar | `.aro-section-header` — `_section-header.css` |

### Forms
| Component | Use for | Entry point |
|---|---|---|
| Form controls | Inputs, selects, checks, switches | Bootstrap `.form-control` / `.form-select` / `.form-check` / `.form-switch` (themed) — `_form.css`, `_form-controls.css`. Form theme: `@AropixelAdmin/Form/layout/theme.html.twig` |
| Rich editor | WYSIWYG content | `EditorType` (Quill) — see the `aropixel-*` form skills |
| Single image / gallery / file | Media fields | `ImageType` / `GalleryType` / `FileType` — see `aropixel-image`, `aropixel-gallery` |
| Radio cards | Mutually-exclusive options as selectable cards | `.aro-radio-card` + `__body` / `__title` / `__desc` / `__price` (drives off the checked radio via `:has()`) — `_radio-cards.css` |
| File upload | A dropzone / file picker | `.aro-fileupload` (label over a hidden input) + `.aro-fileupload-preview` — `_file-upload.css` |
| Icons | Any icon | `{{ ux_icon('lucide:<name>') }}` (ux-icons / Lucide, `currentColor`) |

### Foundations
- **Tokens** — `foundations/_tokens.css`: colours (`--aro-color-*`, `--aro-status-*`, `--aro-accent-*`), spacing (`--aro-space-1…12`), radii (`--aro-radius-*`), shadows (`--aro-shadow-*`), type scale (`--aro-text-xs … --aro-text-3xl`), motion (`--aro-duration-*`, `--aro-ease`). Bootstrap is themed from these in `foundations/_bootstrap-bridge.css`.
- **Theming** — integrators override the semantic `--aro-*` tokens only; see `doc/theming.md`.

---

## Workflow

1. Match the task to a component in the index above.
2. Open its `_<name>.css` header for the exact `Markup:` and classes (or check `docs/catalog.html`).
3. Compose with tokens; obey the golden rules (one CTA, tokens only, no `!important`).
4. For Dialog/Toast, call the JS API — do not rebuild the overlay by hand.

---
name: aropixel-make-crud
description: >
  Generates and completes a Symfony administration CRUD with AropixelAdminBundle.
  Use this skill whenever the user asks to create an admin section, an admin CRUD,
  a management interface for an entity, or to "add the admin" for an entity.
  This skill covers the complete workflow: make:crud command, DataTable completion,
  list template, form template, adding to the admin menu, breadcrumbs, and macros.
  Also use when the user says "generate the admin controller," "create the admin list page,"
  or asks to customize the columns/fields of an already generated CRUD.
---

# Skill: Generating an Admin CRUD with AropixelAdminBundle

## Complete workflow

### Step 1 — Run the command

```bash
php bin/console aropixel:make:crud
```

The terminal will ask for:
1. **Entity Class**: e.g., `App\Entity\MyEntity`
2. **FormType Class**: e.g., `App\Form\MyEntityType`

Generated files:
- `src/Controller/Admin/MyEntityController.php`
- `templates/admin/my_entity/index.html.twig`
- `templates/admin/my_entity/form.html.twig`

---

### Step 2 — Complete the DataTable in the Controller

By default, only the `ID` column is generated. **Always complete** with the relevant columns of the entity.

#### Column selection rules

| Doctrine property type | DataTable Column | Example |
|---|---|---|
| `string`, `text` | Simple label, `orderBy` on the field | `['label' => 'Title', 'orderBy' => 'title']` |
| `date`, `datetime` | `d/m/Y` format in renderJson | `['label' => 'Date', 'orderBy' => 'createdAt', 'style' => 'width:150px;']` |
| `boolean` | ✓/✗ icon or Yes/No label | `['label' => 'Active', 'orderBy' => 'active', 'style' => 'width:80px;']` |
| `integer`, `float` | Simple label | `['label' => 'Price', 'orderBy' => 'price']` |
| ManyToOne relation | `join()` + alias in `orderBy` | See Joins section below |
| ManyToMany / OneToMany relation | Do not put in column, ignore | — |
| `image` (file field) | `media.thumbnail_with_status` macro | See Image section below |

#### Columns to always exclude
- **The ID** — never put the ID as a datatable column
- Passwords, tokens, internal technical fields
- Collections (ManyToMany, OneToMany)
- `updatedAt` fields (unless explicitly needed)

#### Name/label/title column: always clickable via `_link.html.twig`

The main field of the entity (name, title, label, etc.) should be rendered via a `_link.html.twig` partial that makes it clickable to the edit page. In `renderJson`:

```php
$this->renderView('admin/my_entity/_link.html.twig', ['item' => $item]),
```

Create the `templates/admin/my_entity/_link.html.twig` file:

```twig
<a href="{{ path('admin_myentity_edit', {id: item.id}) }}">{{ item.title }}</a>
```

> Adapt `item.title` to the getter of the main field of the entity (`item.name`, `item.label`, etc.) and `admin_myentity_edit` to the route name of the entity.

#### Last column: always the actions
```php
['label' => '', 'orderBy' => '', 'class' => 'no-sort']
```

#### Complete Controller Pattern

```php
#[Route("/", name: "index", methods: ["GET"])]
public function index(DataTableFactory $dataTableFactory): Response
{
    return $dataTableFactory
        ->create(MyEntity::class)
        ->setColumns([
            ['label' => 'Title',      'orderBy' => 'title'],           // rendered via _link.html.twig
            ['label' => 'Category',  'orderBy' => 'c.name'],          // join
            ['label' => 'Date',       'orderBy' => 'createdAt', 'style' => 'width:150px;'],
            ['label' => 'Active',      'orderBy' => 'active',      'style' => 'width:80px;'],
            ['label' => '',           'orderBy' => '',            'class' => 'no-sort'],
        ])
        ->join('category', 'c')          // if ManyToOne relation on category
        ->searchIn(['title', 'c.name'])
        ->setOrderColumn(0)              // default sort on title
        ->setOrderDirection('asc')
        ->renderJson(fn(MyEntity $item) => [
            $this->renderView('admin/my_entity/_link.html.twig', ['item' => $item]),  // clickable main field
            $item->getCategory()?->getName(),
            $item->getCreatedAt()?->format('d/m/Y') ?? '—',
            $item->isActive() ? 'Yes' : 'No',
            $this->renderView('admin/my_entity/_actions.html.twig', ['item' => $item]),
        ])
        ->render('admin/my_entity/index.html.twig');
}
```

File `templates/admin/my_entity/_link.html.twig` to create:

```twig
<a href="{{ path('admin_myentity_edit', {id: item.id}) }}">{{ item.title }}</a>
```

#### Joins (ManyToOne relation)
```php
->join('category', 'c')           // property on the entity, alias
->setColumns([
    ['label' => 'Category', 'orderBy' => 'c.name'],
])
->searchIn(['title', 'c.name'])
```

#### Image column
In `renderJson`, use renderView with the image macro:
```php
$this->renderView('admin/my_entity/_thumbnail.html.twig', ['item' => $item]),
```
```twig
{# _thumbnail.html.twig #}
{% import '@AropixelAdmin/Macro/image.html.twig' as media %}
{{ media.thumbnail_with_status(item, 'image', 'status', path('admin_myentity_edit', {id: item.id})) }}
```

---

### Step 3 — Add the link in the admin menu

After creating the CRUD, **always add a link to the list** in the project's admin menu builder.

Find the class that implements `AdminMenuBuilderInterface` (usually in `src/Component/AdminMenu/` or `src/Menu/`).

Add in the appropriate section method:

```php
$menu->addItem(new Link('My Entity', 'admin_myentity_index', [], ['icon' => 'fas fa-list-ul']));
```

Choose the most relevant section (`buildMainMenu`, `buildSettingsMenu`, etc.) or create a new section if the entity belongs to a distinct domain.

> See the `aropixel-admin-menu` skill for complete details.

---

### Step 4 — Complete the form template `form.html.twig`

Minimal pattern:

```twig
{% extends '@AropixelAdmin/Form/base.html.twig' %}
{% import '@AropixelAdmin/Macro/breadcrumb.html.twig' as nav %}

{% block meta_title %}{% if my_entity.id %}Edit{% else %}Add{% endif %} an item{% endblock %}
{% block header_title %}{% if my_entity.id %}{{ my_entity.title }}{% else %}Add an item{% endif %}{% endblock %}

{% block header_breadcrumb %}
    {{ nav.breadcrumbs([
        { label: 'text.home', url: url('_admin') },
        { label: 'Items', url: url('admin_myentity_index') },
        { label: (my_entity.id ? 'Edit' : 'Add') ~ ' an item' }
    ]) }}
{% endblock %}

{% block mainPanel %}
    <div class="card card-centered card-centered-large">
        <div class="card-body">
            {{ form_rest(form) }}
        </div>
    </div>
{% endblock %}
```

For a form with **tabs** or **collections**, see the Form Templates section below.

---

## Reference: DataTable Component

The `DataTable` component of the AdminBundle simplifies the creation of JSON responses compatible with the [DataTables](https://datatables.net/) jQuery plugin. It provides a Fluent Interface to configure columns, filters, and rendering.

### Basic Usage (Single Action)

The recommended way to use the component is to handle both the HTML page and the JSON data in a single controller action. This avoids duplicating column definitions.

```php
use App\Entity\MyEntity;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;

#[Route("/", name: "index", methods: ["GET"])]
public function index(DataTableFactory $dataTableFactory): Response
{
    return $dataTableFactory
        ->create(MyEntity::class)
        ->setColumns([
            ['label' => 'Title', 'orderBy' => 'title'],   // rendered via _link.html.twig
            ['label' => 'Date', 'orderBy' => 'createdAt', 'style' => 'width:200px;'],
            ['label' => '', 'orderBy' => '', 'class' => 'no-sort'],
        ])
        ->searchIn(['title'])
        ->renderJson(fn(MyEntity $item) => [
            $this->renderView('admin/my_entity/_link.html.twig', ['item' => $item]),  // clickable main field
            $item->getCreatedAt()->format('d/m/Y'),
            $this->renderView('admin/my_entity/_actions.html.twig', ['item' => $item]),
        ])
        ->render('admin/my_entity/index.html.twig');
}
```

File `templates/admin/my_entity/_link.html.twig` to create:

```twig
<a href="{{ path('admin_myentity_edit', {id: item.id}) }}">{{ item.title }}</a>
```

### Fluent Interface Methods

- `setColumns(array $columns)` — defines all columns
- `addColumn(array|DataTableColumn $column)` — adds a column
- `addColumnsIf(bool $condition, array $columns)` — adds columns conditionally
- `join(string $property, string $alias)` — automatic LEFT JOIN
- `searchIn(array $fields)` — enables LIKE search
- `setOrderColumn(?int $index)` — default sort column (index 0)
- `setOrderDirection(?string $direction)` — `'asc'` or `'desc'`
- `filter(callable $filter)` — contextual filter via QueryBuilder
- `useRepositoryMethod(string $methodName)` — custom repo method (must return a `QueryBuilder`)
- `renderJson(callable $transformer)` — data transformer
- `render(string $template, array $parameters = [])` — HTML template

### Classic Mode (pre-loaded data)

```php
return $dataTableFactory
    ->create(MyEntity::class, mode: DataTableInterface::MODE_CLASSIC)
    ->setItems($items)
    ->setColumns([...])
    ->render('admin/my_entity/index.html.twig');
```

Classic mode template:

```twig
{% extends '@AropixelAdmin/List/datatable.html.twig' %}
{% import '@AropixelAdmin/Macro/actions.html.twig' as list %}

{% block datatable_row %}
    <tr>
        <td>{{ item.title }}</td>
        <td class="text-right">
            {{ list.actions(item, path('admin_myentity_edit', {id: item.id}), path('admin_myentity_delete', {id: item.id})) }}
        </td>
    </tr>
{% endblock %}
```

---

## Reference: Form Templates

### Complete structure with tabs

```twig
{% extends '@AropixelAdmin/Form/base.html.twig' %}
{% import '@AropixelAdmin/Macro/breadcrumb.html.twig' as nav %}
{% import '@AropixelAdmin/Macro/forms.html.twig' as forms %}

{% block meta_title %}{% if my_entity.id %}Edit{% else %}Add{% endif %} an item{% endblock %}
{% block header_title %}{% if my_entity.id %}{{ my_entity.name }}{% else %}Add an item{% endif %}{% endblock %}

{% block header_breadcrumb %}
    {{ nav.breadcrumbs([
        { label: 'text.home', url: url('_admin') },
        { label: 'Items', url: url('admin_myentity_index') },
        { label: (my_entity.id ? 'Edit' : 'Add') ~ ' an item' }
    ]) }}
{% endblock %}

{% block tabbable %}
    {{ forms.tabs([
        { id: 'panel-tab-general', label: 'General' },
        { id: 'panel-tab-extra', label: 'Details' },
    ]) }}
{% endblock %}

{% block mainPanel %}
    <div class="tab-pane active" id="panel-tab-general">
        <div class="card card-centered card-centered-large">
            <div class="card-body">
                {{ form_row(form.name) }}
                {{ form_row(form.description) }}
            </div>
        </div>
    </div>
    <div class="tab-pane" id="panel-tab-extra">
        <div class="card card-centered card-centered-large">
            <div class="card-body">
                {{ form_row(form.image) }}
            </div>
        </div>
    </div>
{% endblock %}
```

### Collections in a form

```twig
<div class="form-group mt-4">
    <div class="d-flex justify-content-between align-items-center mb-2 w-100">
        <label class="control-label">Items</label>
        <a class="btn btn-primary btn-xs" data-form-collection-add="{{ form.items.vars.id }}">
            <i class="fa fa-plus"></i> Add
        </a>
    </div>
    {{ form_widget(form.items, {'attr': {'class': 'w-100'}}) }}
</div>
```

### Available horizontal ratios

- `.form-horizontal-20-80`
- `.form-horizontal-30-70`
- `.form-horizontal-33-66`
- `.form-horizontal-40-60`
- `.form-horizontal-50-50`

```php
// In FormType
$builder->add('name', TextType::class, [
    'row_attr' => ['class' => 'form-horizontal-40-60'],
]);
```

---

## Reference: Twig Macros

### Actions (`@AropixelAdmin/Macro/actions.html.twig`)

```twig
{% import '@AropixelAdmin/Macro/actions.html.twig' as list %}
{{ list.actions(item, path('admin_entity_edit', {id: item.id}), path('admin_entity_delete', {id: item.id})) }}
```

| Parameter | Description |
|---|---|
| `item` | The entity (for the delete CSRF token) |
| `edit_path` | Edit URL (optional) |
| `delete_path` | Delete URL (optional) |
| `delete_confirm_msg` | Custom confirmation message (optional) |

### Breadcrumb (`@AropixelAdmin/Macro/breadcrumb.html.twig`)

```twig
{% import '@AropixelAdmin/Macro/breadcrumb.html.twig' as nav %}
{{ nav.breadcrumbs([
    { label: 'text.home', url: url('_admin') },
    { label: 'Entities', url: url('admin_entity_index') },
    { label: 'Edit' }
]) }}
```

### Image (`@AropixelAdmin/Macro/image.html.twig`)

```twig
{% import '@AropixelAdmin/Macro/image.html.twig' as media %}
{{ media.thumbnail_with_status(item, 'image', 'status', path('admin_entity_edit', {id: item.id})) }}
```

| Parameter | Default | Description |
|---|---|---|
| `item` | — | The entity |
| `image_field` | `'image'` | Image property name |
| `status_field` | `'status'` | Status property name |
| `edit_path` | — | URL of the link around the thumbnail |
| `filter` | `'admin_thumbnail'` | LiipImagine filter |
| `height` | `60` | Height in pixels |

### Tabs (`@AropixelAdmin/Macro/forms.html.twig`)

```twig
{% import '@AropixelAdmin/Macro/forms.html.twig' as forms %}
{% block tabbable %}
    {{ forms.tabs([
        { id: 'panel-tab-general', label: 'General' },
        { id: 'panel-tab-extra', label: 'Extra' },
    ]) }}
{% endblock %}
```

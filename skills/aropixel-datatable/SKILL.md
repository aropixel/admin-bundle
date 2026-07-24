---
name: aropixel-datatable
description: >
  Modify, configure, or debug a DataTable in a Symfony project using
  AropixelAdminBundle. Use this skill whenever the user asks to create an
  admin list, or to add or remove a column, change the default sorting,
  add a filter, a join, a search, or to adapt the behavior of an existing admin list.
  Also use for advanced cases: conditional column, custom repository method,
  classic mode (pre-loaded data), or any issue related to the
  DataTableFactory component. Covers the PHP controller and the associated Twig template.
---

# Skill: Configuring the DataTable with AropixelAdminBundle

## Anatomy of a DataTable

```php
return $dataTableFactory
    ->create(MyEntity::class)       // (1) Target entity
    ->join('relation', 'alias')     // (2) Joins (optional)
    ->setColumns([...])             // (3) Columns
    ->searchIn(['field', 'alias.field']) // (4) Full-text search
    ->setOrderColumn(0)             // (5) Default sort: column index
    ->setOrderDirection('desc')     //     direction: 'asc' | 'desc'
    ->filter(fn($qb) => ...)        // (6) Contextual filter (optional)
    ->renderJson(fn($item) => [...]) // (7) JSON data per row
    ->render('admin/.../index.html.twig'); // (8) HTML template
```

---

## Columns — `setColumns()` / `addColumn()` / `addColumnsIf()`

### Column format

```php
[
    'label'   => 'My label',          // header text
    'orderBy' => 'myField',           // Doctrine field for sorting ('' = no sorting)
    'class'   => 'no-sort',           // CSS classes on the <th> (e.g., disable sorting)
    'style'   => 'width:120px;',      // inline CSS style on the <th>
    'data'    => ['type' => 'date-euro'], // data-* attributes on the <th>
]
```

### Adding / replacing columns

```php
// Replaces all columns at once
->setColumns([
    ['label' => 'Title',   'orderBy' => 'title'],
    ['label' => 'Date',    'orderBy' => 'createdAt', 'style' => 'width:150px;'],
    ['label' => '',        'orderBy' => '',           'class' => 'no-sort'],
])

// Adds a column to the existing list
->addColumn(['label' => 'Status', 'orderBy' => 'status'])

// Adds several columns conditionally
->addColumnsIf($showCategory, [
    ['label' => 'Category', 'orderBy' => 'c.name'],
])
```

### Actions column — always last
```php
['label' => '', 'orderBy' => '', 'class' => 'no-sort']
```
And in `renderJson`, the last element of the array:
```php
$this->renderView('admin/entity/_actions.html.twig', ['item' => $item])
```

---

## Joins — `join()`

To display or sort on a property of a linked entity (ManyToOne):

```php
->join('category', 'c')         // 'category' relation on the entity, alias 'c'
->join('author', 'a')           // multiple join() can be chained
->setColumns([
    ['label' => 'Category', 'orderBy' => 'c.name'],
    ['label' => 'Author',    'orderBy' => 'a.lastName'],
])
->searchIn(['title', 'c.name', 'a.lastName'])
```

> The join is an automatic `LEFT JOIN`. The alias is used everywhere: `orderBy`, `searchIn`, and in `filter()`.

---

## Full-text search — `searchIn()`

Enables automatic `LIKE` clauses on the listed fields:

```php
->searchIn(['title', 'description', 'c.name'])
```

- Works with entity fields and join aliases
- Do not include `boolean`, `date` fields, or complex relations

---

## Default sort — `setOrderColumn()` / `setOrderDirection()`

```php
->setOrderColumn(1)          // index of the column in setColumns() (starts at 0)
->setOrderDirection('desc')  // 'asc' or 'desc'
```

---

## Contextual filter — `filter()`

To restrict data without changing the base query:

```php
->filter(function(QueryBuilder $qb) {
    $qb->andWhere('e.active = :active')
       ->setParameter('active', true);
})

// With a controller variable:
->filter(function(QueryBuilder $qb) use ($currentUser) {
    $qb->andWhere('e.owner = :owner')
       ->setParameter('owner', $currentUser);
})
```

---

## Custom repository method — `useRepositoryMethod()`

When the query logic is too complex for `filter()`:

```php
->useRepositoryMethod('findArchivedWithStats')
```

The method in the repository must return a `QueryBuilder`:

```php
// src/Repository/MyEntityRepository.php
public function findArchivedWithStats(): QueryBuilder
{
    return $this->createQueryBuilder('e')
        ->leftJoin('e.stats', 's')
        ->andWhere('e.archived = true')
        ->addSelect('s');
}
```

---

## Classic Mode (pre-loaded data)

For lists without AJAX, with data already retrieved:

```php
use Aropixel\AdminBundle\Component\DataTable\DataTableInterface;

$items = $this->repository->findAll();

return $dataTableFactory
    ->create(MyEntity::class, mode: DataTableInterface::MODE_CLASSIC)
    ->setItems($items)
    ->setColumns([
        ['label' => 'Title', 'orderBy' => 'title'],
        ['label' => '',      'orderBy' => '', 'class' => 'no-sort'],
    ])
    ->render('admin/entity/index.html.twig');
```

Classic mode template — use the `datatable_row` block:

```twig
{% extends '@AropixelAdmin/List/datatable.html.twig' %}
{% import '@AropixelAdmin/Macro/actions.html.twig' as list %}

{% block datatable_row %}
    <tr>
        <td>{{ item.title }}</td>
        <td class="text-right">
            {{ list.actions(item, path('admin_entity_edit', {id: item.id}), path('admin_entity_delete', {id: item.id})) }}
        </td>
    </tr>
{% endblock %}
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

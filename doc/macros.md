# Twig Macros Documentation

This documentation describes the reusable Twig macros provided by `AropixelAdminBundle`.

## Actions Macro

The `actions` macro generates a dropdown button containing standard actions (like "Edit" and "Delete") for an item in a list or a table.

### Location

File: `@AropixelAdmin/Macro/actions.html.twig`

### Usage

To use this macro, you first need to import it in your Twig template:

```twig
{% import '@AropixelAdmin/Macro/actions.html.twig' as list %}

{# ... #}

{{ list.actions(item, edit_path, delete_path, delete_confirm_msg) }}
```

### Parameters

| Parameter | Type | Description |
| :--- | :--- | :--- |
| `item` | `object` | The entity instance (used to generate the CSRF token for deletion). |
| `edit_path` | `string` | (Optional) The URL for the "Edit" action. If omitted, the edit link will not be displayed. |
| `delete_path` | `string` | (Optional) The URL for the "Delete" action. If omitted, the delete link will not be displayed. |
| `delete_confirm_msg` | `string` | (Optional) A custom confirmation message for the deletion. Defaults to the translation of `text.confirm_delete`. |

### Example

In a DataTable row:

```twig
{% block datatable_body %}
    <td>{{ item.id }}</td>
    <td>{{ item.title }}</td>
    <td class="text-right">
        {{ list.actions(
            item,
            path('admin_event_edit', {id: item.id}),
            path('admin_event_delete', {id: item.id})
        ) }}
    </td>
{% endblock %}
```

## Breadcrumb Macro

The `breadcrumb` macro generates a navigation breadcrumb list from an array of items.

### Location

File: `@AropixelAdmin/Macro/breadcrumb.html.twig`

### Usage

To use this macro, you first need to import it in your Twig template:

```twig
{% import '@AropixelAdmin/Macro/breadcrumb.html.twig' as nav %}

{# ... #}

{% block header_breadcrumb %}
    {{ nav.breadcrumbs([
        { label: 'text.home', url: url('_admin') },
        { label: 'Programmation' },
        { label: 'Modifier un artiste' }
    ]) }}
{% endblock %}
```

### Parameters

| Parameter | Type | Description |
| :--- | :--- | :--- |
| `items` | `array` | An array of objects/associative arrays. Each item should have a `label` and an optional `url`. |

Each item in the `items` array can have:
- `label`: (Required) The text to display (it will be passed through the `|trans` filter).
- `url`: (Optional) The URL for the link. If omitted, the label will be displayed without a link.

The macro automatically adds the `active` class to the last item in the list.

## Adding New Macros

When adding new macros to the bundle:
1. Create a new file in `src/Resources/views/Macro/`.
2. Document the macro in this file following the same structure.

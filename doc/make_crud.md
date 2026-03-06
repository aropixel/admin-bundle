# CRUD Generator (make:crud)

The `AropixelAdminBundle` provides a custom command to quickly generate a CRUD (Create, Read, Update, Delete) interface based on an existing `FormType`.

In line with the bundle's philosophy, this command does not aim to provide a "one-size-fits-all" solution. Instead, it generates the boilerplate code (Controller and Templates) that you can then fully customize to fit your specific needs.

## Usage

To generate a new CRUD, run the following command:

```bash
php bin/console aropixel:make:crud
```

The command will ask you for:
1.  **Entity Class**: The full namespace of your entity (e.g., `App\Entity\MyEntity`).
2.  **FormType Class**: The full namespace of your FormType (e.g., `App\Form\MyEntityType`).

## Generated Files

The command generates three main files:

### 1. Controller
Created in `src/Controller/Admin/`. It includes:
-   **index**: A route using the `DataTableFactory` to list your entities.
-   **new**: A route to create a new entity using your `FormType`.
-   **edit**: A route to edit an existing entity.
-   **delete**: A route to delete an entity with CSRF protection.

### 2. List Template
Created in `templates/admin/[entity]/index.html.twig`. It extends `@AropixelAdmin/List/datatable.html.twig` and is pre-configured to work with the Controller's DataTable.

### 3. Form Template
Created in `templates/admin/[entity]/form.html.twig`. It extends `@AropixelAdmin/Form/base.html.twig` and provides a consistent look and feel for both "new" and "edit" actions.

## Customization

### DataTable Columns
By default, only the `ID` column is generated in the `index` action. You should manually add the columns you want to display in your Controller:

```php
// src/Controller/Admin/MyEntityController.php

public function index(DataTableFactory $dataTableFactory, DataTableRowFactoryInterface $rowFactory): Response
{
    return $dataTableFactory
        ->create(MyEntity::class)
        ->setColumns([
            ['label' => 'ID', 'field' => 'id'],
            ['label' => 'Title', 'field' => 'title'],
            ['label' => 'Created At', 'field' => 'createdAt'],
            ['label' => '', 'field' => '', 'class' => 'no-sort'],
        ])
        ->render($rowFactory);
}
```

And update the template accordingly:

```twig
{# templates/admin/my_entity/index.html.twig #}

{% block datatable_body %}
    <td>{{ item.id }}</td>
    <td>{{ item.title }}</td>
    <td>{{ item.createdAt|date('d/m/Y') }}</td>
    <td class="text-right">
        {{ dt.dropdown(item, path('admin_myentity_edit', {id: item.id}), path('admin_myentity_delete', {id: item.id})) }}
    </td>
{% endblock %}
```

### Form Fields
The generated form template uses `{{ form_rest(form) }}`. You can customize the layout by overriding the `formbody` or `mainPanel` blocks as described in the [Form Templates documentation](form_templates.md).

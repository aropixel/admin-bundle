# DataTable Component

The `DataTable` component of the AdminBundle simplifies the creation of JSON responses compatible with the [DataTables](https://datatables.net/) jQuery plugin. It provides a Fluent Interface to configure columns, filters, and rendering.

## Basic Usage (Single Action)

The recommended way to use the component is to handle both the HTML page and the JSON data in a single controller action. This avoids duplicating column definitions.

```php
use App\Entity\Event;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;

#[Route("/", name: "index", methods: ["GET"])]
public function index(DataTableFactory $dataTableFactory): Response
{
    return $dataTableFactory
        ->create(Event::class)
        ->setColumns([
            ['label' => 'Title', 'field' => 'title'],
            ['label' => 'Date', 'field' => 'startDate', 'style' => 'width:200px;'],
            ['label' => '', 'field' => '', 'class' => 'no-sort'],
        ])
        ->searchIn(['title'])
        ->renderJson(fn(Event $event) => [
            $event->getTitle(),
            $event->getStartDate()->format('d/m/Y'),
            $this->renderView('admin/event/_actions.html.twig', ['item' => $event]),
        ])
        ->render('admin/event/index.html.twig');
}
```

### How it works:
1. `renderJson()`: Detects if the current request is an AJAX request from DataTables. If so, it immediately returns a `JsonResponse` with the transformed data.
2. `render()`: If it wasn't an AJAX request, it renders the specified Twig template, automatically passing the `dataTable` object to the view.

## Column Configuration

Columns can be defined in three ways:
1. Via an associative array (automatically converted to `DataTableColumn`).
2. Via a `DataTableColumn` instance.
3. Individually or in batches.

### Configuration Array Format
The array can contain the following keys:
- `label`: Label displayed in the header.
- `field`: Entity field used for sorting (if applicable).
- `style`: CSS `style` attribute to add to the cell.
- `class`: CSS classes to add to the cell (e.g., `no-sort` to disable sorting on a column).
- `data`: Associative array of custom `data-` attributes to add to the `<th>` tag (e.g., `['type' => 'date-euro']` becomes `data-type="date-euro"`).

## Sorting Configuration

You can define the default sorting for the table:

```php
$dataTableFactory
    ->create(Event::class)
    ->setOrderColumn(2)          // Index of the column (starting from 0)
    ->setOrderDirection('desc')  // 'asc' or 'desc'
    // ...
```

These values are automatically rendered in the Twig template as `data-order-column` and `data-order-direction` attributes.

## Repository Management

### Automatic Search and Sort
The component can automatically handle search (LIKE clauses) and sorting (ORDER BY) if you provide the searchable fields:

```php
$dataTableFactory
    ->create(Event::class)
    ->setColumns([
        ['label' => 'Title', 'field' => 'title'],
        ['label' => 'Date', 'field' => 'createdAt'],
        ['label' => '', 'field' => '', 'class' => 'no-sort'],
    ])
    ->searchIn(['title', 'subTitle']) // Enable automatic search on these fields
    // ...
```

By default, the component uses the `DefaultDataTableRepository`. If `searchIn()` is used or if no custom repository method is specified, it will:
1. Automatically generate the `QueryBuilder` for the entity.
2. Apply `LIKE` conditions on the fields specified in `searchIn()`.
3. Apply `ORDER BY` based on the clicked column and its `field` configuration.

### Customizing the Method
If you need complex logic, you can change the method called in your Doctrine repository:

```php
$dataTableFactory
    ->create(Event::class)
    ->useRepositoryMethod('getArchivedEventsQuery')
    // ...
```

### Contextual Filtering (Inline Filter)
Similar to the Select2 component, you can add dynamic constraints:

```php
$dataTableFactory
    ->create(Event::class)
    ->filter(function(QueryBuilder $qb) {
        $qb->andWhere('e.active = :active')->setParameter('active', true);
    })
    // ...
```

## Fluent Interface Methods

### `setColumns(array $columns): self`
Defines the set of columns. Overwrites any previously added columns.

### `addColumn(array|DataTableColumn $column): self`
Adds a column to the existing list.

### `addColumnsIf(bool $condition, array $columns): self`
Adds multiple columns only if the condition is met.

### `useRepositoryMethod(string $methodName): self`
Defines the Doctrine repository method to call to retrieve the `QueryBuilder`.

### `filter(callable $filter): self`
Adds a callback function to modify the `QueryBuilder` before executing the query.

### `searchIn(array $fields): self`
Enables automatic search on the specified entity fields.

### `setOrderColumn(?int $index): self`
Sets the default column index for sorting.

### `setOrderDirection(?string $direction): self`
Sets the default sorting direction (`asc` or `desc`).

### `renderJson(callable $transformer): Response|self`
If the request is an AJAX request, it returns a `JsonResponse`. Otherwise, it stores the transformer and returns `$this`.

### `render(string $template, array $parameters = []): Response`
Renders the Twig template and returns a `Response`.

## Choosing the Mode (XHR or Classic)

By default, the `DataTableFactory` creates an object in XHR (AJAX) mode.

### XHR Mode (Default)
The table is loaded via AJAX. If you use the single-action approach, the AJAX URL is automatically the current URL.

### Classic Mode (Pre-loaded Data)
The table is rendered directly with provided data.

```php
return $dataTableFactory
    ->create(Event::class, mode: DataTableInterface::MODE_CLASSIC)
    ->setItems($events)
    ->setColumns([...])
    ->render('admin/event/list.html.twig');
```

## Action Builder (Twig)

To simplify the creation of action menus in columns, a Twig macro is available.

### Example in Classic Mode

```twig
{% extends '@AropixelAdmin/List/datatable.html.twig' %}

{% block datatable_row %}
    <tr>
        <td>{{ item.title }}</td>
        <td>{{ item.startDate|date('d/m/Y') }}</td>
        <td class="text-right">
            {{ dt.dropdown(item, path('admin_event_edit', {id: item.id}), path('admin_event_delete', {id: item.id})) }}
        </td>
    </tr>
{% endblock %}
```

## Polymorphic Twig Integration

The `@AropixelAdmin/List/datatable.html.twig` template automatically adapts to the `dataTable` object and its mode.

### AJAX Mode (Simplified)
If you use XHR mode with the single-action pattern, you don't even need to provide `ajax_url`.

```twig
{% extends '@AropixelAdmin/List/datatable.html.twig' %}
```

### Manual Mode (Backward Compatibility)
You can still manually define headers and body if you do not pass a `dataTable` object.

```twig
{% extends '@AropixelAdmin/List/datatable.html.twig' %}

{% block datatable_header %}
    <th>Title</th>
{% endblock %}

{% block datatable_body %}
    {% for event in events %}
        <tr>
            <td>{{ event.title }}</td>
        </tr>
    {% endfor %}
{% endblock %}
```

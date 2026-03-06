# DataTable Component

The `DataTable` component of the AdminBundle simplifies the creation of JSON responses compatible with the [DataTables](https://datatables.net/) jQuery plugin. It provides a Fluent Interface to configure columns, filters, and rendering.

## Basic Usage

The component is primarily used via the `DataTableFactory` service. Here is a typical example in a controller:

```php
use App\Entity\Event;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;
use Aropixel\AdminBundle\Component\DataTable\Row\DataTableRowFactoryInterface;

public function list(DataTableFactory $dataTableFactory, DataTableRowFactoryInterface $rowFactory): Response
{
    return $dataTableFactory
        ->create(Event::class)
        ->setColumns([
            ['label' => '', 'field' => '', 'class' => 'no-sort'],
            ['label' => 'Date', 'field' => 'startDate', 'style' => 'width:200px;'],
            ['label' => 'Title', 'field' => 'title'],
            ['label' => 'City', 'field' => 'place.city'],
            ['label' => 'Location', 'field' => 'place.label', 'style' => 'width:200px;'],
            ['label' => 'Status', 'field' => 'eventStatus'],
        ])
        ->addColumnsIf($this->isMultiService(), [
            ['label' => 'Service', 'field' => '', 'class' => 'no-sort']
        ])
        ->addColumn(['label' => '', 'field' => '', 'class' => 'no-sort'])
        ->render($rowFactory);
}
```

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

By default, the component uses the `DefaultDataTableRepository` which looks for a `getQueryDataTable(DataTableContext $context)` method in the entity's Doctrine repository.

### Customizing the Method
You can change the method called in your Doctrine repository:

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

### `setOrderColumn(?int $index): self`
Sets the default column index for sorting.

### `setOrderDirection(?string $direction): self`
Sets the default sorting direction (`asc` or `desc`).

### `render(DataTableRowFactoryInterface $factory): Response`
Generates the final JSON response. This is an alias for `getResponse()`.

## Choosing the Mode (XHR or Classic)

By default, the `DataTableFactory` creates an object in XHR (AJAX) mode. You can specify the mode during creation or via the `setMode()` method.

### XHR Mode (Default)
The table is loaded via AJAX. You must provide a URL for the data source.

```php
return $dataTableFactory
    ->create(Event::class, [], DataTableInterface::MODE_XHR)
    ->setColumns([...])
    ->render($rowFactory);
```

### Classic Mode (Pre-loaded Data)
The table is rendered directly with the provided data.

```php
return $this->render('admin/event/list.html.twig', [
    'dataTable' => $dataTableFactory
        ->create(Event::class, [], DataTableInterface::MODE_CLASSIC)
        ->setColumns([
            ['label' => 'Title', 'field' => 'title'],
            ['label' => 'Actions', 'field' => '', 'class' => 'no-sort'],
        ])
        ->setItems($events)
]);
```

## Action Builder (Twig)

To simplify the creation of action menus in columns, a Twig macro is available.

### Example in Classic Mode

```twig
{% extends '@AropixelAdmin/List/datatable.html.twig' %}

{% block datatable_body %}
    <td>{{ item.title }}</td>
    <td class="text-right">
        {{ dt.dropdown(item, path('admin_event_edit', {id: item.id}), path('admin_event_delete', {id: item.id})) }}
    </td>
{% endblock %}
```

The `dt.dropdown` macro accepts the following arguments:
- `item`: The row object.
- `edit_path`: (Optional) The edit URL.
- `delete_path`: (Optional) The delete URL.
- `delete_confirm_msg`: (Optional) Deletion confirmation message.

## Polymorphic Twig Integration

The `@AropixelAdmin/List/datatable.html.twig` template automatically adapts whether you pass a `dataTable` object or not, and according to its mode.

### AJAX Mode (Simplified)
If you use XHR mode, the template automatically generates the headers.

```twig
{% extends '@AropixelAdmin/List/datatable.html.twig' %}
{% set ajax_url = path('admin_event_xhr') %}
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

# Select2 Component

The `Select2` component of the AdminBundle simplifies the creation of JSON endpoints compatible with the [Select2](https://select2.org/) JavaScript component. It uses a **Data Providers** system to decouple the data retrieval logic from your controllers.

## General Overview

The component relies on three main elements:
1. The `Select2` class: The main service to inject into your controllers.
2. The `Select2DataProviderInterface` interface: To be implemented for each data source.
3. Symfony's Tag system: To automatically register your providers.

## Configuring a Data Provider

For each entity or data source you want to expose via Select2, you must create a class implementing `Select2DataProviderInterface`.

```php
namespace App\Select2;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Aropixel\AdminBundle\Component\Select2\Select2DataProviderInterface;
use Doctrine\ORM\QueryBuilder;

class CustomerSelect2Provider implements Select2DataProviderInterface
{
    public function __construct(private CustomerRepository $repository) {}

    public function supports(string $alias): bool
    {
        // The alias used in the controller to call this provider
        return $alias === 'customer';
    }

    public function getRootAlias(): string
    {
        // The alias used in the QueryBuilder (must match the one in createQueryBuilder)
        return 'c';
    }

    public function getQueryBuilder(string $searchTerm): QueryBuilder
    {
        // Returns the base QueryBuilder with the search logic
        return $this->repository->createQueryBuilder('c')
            ->where('c.email LIKE :q OR c.firstName LIKE :q OR c.lastName LIKE :q')
            ->setParameter('q', '%'.$searchTerm.'%');
    }
}
```

Thanks to autoconfiguration, this service will be automatically detected by the AdminBundle.

## Use in a Controller

### With a Data Provider

Once your provider is created, you can use it fluently in your controller.

```php
use Aropixel\AdminBundle\Component\Select2\Select2;
use App\Entity\Customer;

public function select2(Select2 $select2): Response
{
    return $select2
        ->withProvider('customer') // Uses the alias defined in the provider
        ->filter(function(QueryBuilder $qb) {
            // Optional: Add additional contextual filters
            $qb->andWhere('c.active = :active')->setParameter('active', true);
        })
        ->render(); // By default, uses getId() and __toString() for full_name
}
```

If you need a custom format, you can provide a transformer.

If the transformer returns a string, it will be used as the `full_name` and the default `id` will be used.
If it returns an array, you can omit the `id` to use the default one. The `text` key is also supported and automatically mapped to `full_name` for backward compatibility.

```php
        // Transformer returning a string
        ->render(fn(Customer $customer) => $customer->getFullName())

        // Transformer returning an array without the 'id' key
        ->render(fn(Customer $customer) => [
            'full_name' => sprintf('%s (%s)', $customer->getFullName(), $customer->getEmail()),
        ])

        // Transformer returning an array with the legacy 'text' key
        ->render(fn(Customer $customer) => [
            'id'   => $customer->getId(),
            'text' => $customer->getFullName(),
        ])

        // Transformer returning an indexed array (id, full_name)
        ->render(fn(Customer $customer) => [
            $customer->getId(),
            $customer->getFullName(),
        ]);
```

### With Automatic Search (`searchIn`)

If you don't want to create a dedicated provider, you can use the automatic search on specific fields.

```php
use Aropixel\AdminBundle\Component\Select2\Select2;
use App\Entity\Customer;

public function select2(Select2 $select2): Response
{
    return $select2
        ->withEntity(Customer::class)
        ->searchIn(['firstName', 'lastName', 'email'])
        ->render(); // Uses getId() and __toString() by default
}
```

### With Automatic Search and Filtering

You can combine `searchIn` and `filter` for a flexible "out-of-the-box" experience.

```php
use Aropixel\AdminBundle\Component\Select2\Select2;
use App\Entity\Customer;

public function select2(Select2 $select2): Response
{
    return $select2
        ->withEntity(Customer::class)
        ->searchIn(['firstName', 'lastName', 'email'])
        ->filter(function(QueryBuilder $qb) {
            $qb->andWhere('e.active = :active')->setParameter('active', true);
        })
        ->render();
}
```

## Available Methods

### `withProvider(string $alias): self`
Sets the data provider to use based on its alias.

### `withEntity(string $className): self`
Sets the entity class to use when no provider is defined.

### `searchIn(array $fields): self`
Enables automatic search on the specified fields (using `LIKE %search%`). Works both with `withEntity()` and `withProvider()`.

### `filter(callable $callback): self`
Allows applying additional filters on the `QueryBuilder`. The closure receives the `QueryBuilder` object as a parameter. It can be used in combination with `searchIn()` for a mix of automatic search and custom filtering.

### `render(callable $transformer = null): Response`
Executes the search (automatically handling pagination and counting) and returns a `JsonResponse`. If no transformer is provided, it uses `id` and `__toString()` to format the results as `['id' => ..., 'full_name' => ...]`.

## JSON Response Format

The response generated by `render()` follows the standard Select2 format:

```json
{
  "results": [
    { "id": 1, "full_name": "John Doe (john@example.com)" },
    { "id": 2, "full_name": "Jane Smith (jane@example.com)" }
  ],
  "pagination": {
    "more": true
  },
  "total_count": 45
}
```

<?php

namespace Aropixel\AdminBundle\Component\DataTable;

use Aropixel\AdminBundle\Component\DataTable\Column\DataTableColumn;
use Aropixel\AdminBundle\Component\DataTable\Context\DataTableContext;
use Aropixel\AdminBundle\Component\DataTable\Row\DataTableRowFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

interface DataTableInterface
{
    public const MODE_XHR = 'xhr';
    public const MODE_CLASSIC = 'classic';

    public function getClassName(): string;

    public function getContext(): DataTableContext;

    public function getMode(): string;

    /**
     * @return DataTableInterface&Response
     */
    public function setMode(string $mode): self;

    /**
     * @return DataTableInterface&Response
     */
    public function setItems(array $items): self;

    /**
     * @return array<mixed>
     */
    public function getItems(): array;

    public function getOrderColumn(): ?int;

    /**
     * @return DataTableInterface&Response
     */
    public function setOrderColumn(?int $orderColumn): self;

    public function getOrderDirection(): ?string;

    /**
     * @return DataTableInterface&Response
     */
    public function setOrderDirection(?string $orderDirection): self;

    /**
     * @param array<int, array<string, mixed>>|DataTableColumn[] $columns
     * @return DataTableInterface&Response
     */
    public function setColumns(array $columns): self;

    /**
     * @param array|DataTableColumn $column
     * @return DataTableInterface&Response
     */
    public function addColumn(array|DataTableColumn $column): self;

    /**
     * @param DataTableColumn[] $columns
     * @return DataTableInterface&Response
     */
    public function addColumnsIf(bool $condition, array $columns): self;

    /**
     * @return DataTableInterface&Response
     */
    public function useRepositoryMethod(string $methodName): self;

    /**
     * @return DataTableInterface&Response
     */
    public function filter(callable $filter): self;

    /**
     * @param string[] $fields
     * @return DataTableInterface&Response
     */
    public function searchIn(array $fields): self;

    public function getSearchFields(): array;

    /**
     * @return DataTableInterface&Response
     */
    public function join(string $property, string $alias): self;

    /**
     * @return array<string, string>
     */
    public function getJoins(): array;

    /**
     * @return DataTableInterface&Response
     */
    public function renderJson(callable $transformer): self;

    /**
     * @param string $template
     * @param array<string, mixed> $parameters
     * @return DataTableInterface&Response
     */
    public function render(string $template, array $parameters = []): self;

    /**
     * @param callable|DataTableRowFactoryInterface $transformer
     */
    public function getResponse(callable|DataTableRowFactoryInterface $transformer): Response;

    public function getTotal(): int;
}

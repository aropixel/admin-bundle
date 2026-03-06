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

    public function setMode(string $mode): self;

    /**
     * @param array<mixed> $items
     */
    public function setItems(array $items): self;

    /**
     * @return array<mixed>
     */
    public function getItems(): array;

    public function getOrderColumn(): ?int;

    public function setOrderColumn(?int $orderColumn): self;

    public function getOrderDirection(): ?string;

    public function setOrderDirection(?string $orderDirection): self;

    /**
     * @param DataTableColumn[] $columns
     */
    public function setColumns(array $columns): self;

    /**
     * @param array|DataTableColumn $column
     */
    public function addColumn(array|DataTableColumn $column): self;

    /**
     * @param DataTableColumn[] $columns
     */
    public function addColumnsIf(bool $condition, array $columns): self;

    public function useRepositoryMethod(string $methodName): self;

    public function filter(callable $filter): self;

    public function render(DataTableRowFactoryInterface $dataTableRowFactory): Response;

    public function getResponse(DataTableRowFactoryInterface $dataTableRowFactory): Response;

    public function getTotal(): int;
}

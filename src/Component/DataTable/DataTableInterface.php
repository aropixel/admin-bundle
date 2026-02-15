<?php

namespace Aropixel\AdminBundle\Component\DataTable;

use Aropixel\AdminBundle\Component\DataTable\Column\DataTableColumn;
use Aropixel\AdminBundle\Component\DataTable\Context\DataTableContext;
use Aropixel\AdminBundle\Component\DataTable\Row\DataTableRowFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

interface DataTableInterface
{
    public function getClassName(): string;

    public function getContext(): DataTableContext;

    /**
     * @return DataTableColumn[]
     */
    public function getColumns(): array;

    /**
     * @return mixed[]
     */
    public function getRowsContent(): iterable;

    /**
     * @return array<array<string>>
     */
    public function getRows(DataTableRowFactoryInterface $dataTableRowFactory): array;

    public function getResponse(DataTableRowFactoryInterface $dataTableRowFactory): Response;

    public function getTotal(): int;
}

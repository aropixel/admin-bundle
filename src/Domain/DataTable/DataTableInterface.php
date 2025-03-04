<?php

namespace Aropixel\AdminBundle\Domain\DataTable;

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

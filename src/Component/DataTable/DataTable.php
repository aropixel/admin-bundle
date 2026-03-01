<?php

namespace Aropixel\AdminBundle\Component\DataTable;

use Aropixel\AdminBundle\Component\DataTable\Column\DataTableColumn;
use Aropixel\AdminBundle\Component\DataTable\Context\DataTableContext;
use Aropixel\AdminBundle\Component\DataTable\Repository\DataTableRepositoryInterface;
use Aropixel\AdminBundle\Component\DataTable\Row\DataTableRowFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

class DataTable implements DataTableInterface
{
    /**
     * @param class-string      $className
     * @param DataTableColumn[] $columns
     */
    public function __construct(
        private readonly string $className,
        private readonly array $columns,
        private readonly DataTableContext $context,
        private readonly DataTableRepositoryInterface $dataTableRepository
    ) {
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getContext(): DataTableContext
    {
        return $this->context;
    }

    /**
     * @return DataTableColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return mixed[]
     */
    public function getRowsContent(): iterable
    {
        return $this->dataTableRepository->getRowsContent($this);
    }

    /**
     * @return array<array<string>>
     */
    public function getRows(DataTableRowFactoryInterface $dataTableRowFactory): array
    {
        $rows = [];
        $rowsContent = $this->getRowsContent();

        foreach ($rowsContent as $rowContent) {
            $rows[] = $dataTableRowFactory->createRow($rowContent);
        }

        return $rows;
    }

    public function getTotal(): int
    {
        return $this->dataTableRepository->count($this);
    }

    public function getResponse(DataTableRowFactoryInterface $dataTableRowFactory): Response
    {
        $count = $this->getTotal();

        $records = [];
        $records['data'] = $this->getRows($dataTableRowFactory);
        $records['order'] = [];
        $records['draw'] = $this->context->getDraw();
        $records['recordsTotal'] = $count;
        $records['recordsFiltered'] = $count;

        $http_response = new Response(json_encode($records));
        $http_response->headers->set('Content-Type', 'application/json');

        return $http_response;
    }
}

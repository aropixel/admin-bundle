<?php

namespace Aropixel\AdminBundle\Infrastructure\DataTable;

use Aropixel\AdminBundle\Domain\DataTable\DataTableContext;
use Aropixel\AdminBundle\Domain\DataTable\DataTableInterface;
use Aropixel\AdminBundle\Domain\DataTable\DataTableRepositoryInterface;
use Aropixel\AdminBundle\Domain\DataTable\DataTableRowFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

class DataTable implements DataTableInterface
{
    private string $className;

    private array $columns;

    private DataTableContext $context;
    private DataTableRepositoryInterface $dataTableRepository;

    /**
     * @param string $className
     * @param array $columns
     * @param DataTableContext $context
     */
    public function __construct(string $className, array $columns, DataTableContext $context, DataTableRepositoryInterface $dataTableRepository)
    {
        $this->className = $className;
        $this->columns = $columns;
        $this->context = $context;
        $this->dataTableRepository = $dataTableRepository;
    }

    public function getContext(): DataTableContext
    {
        return $this->context;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getRows(DataTableRowFactoryInterface $dataTableRowFactory): array
    {
        $this->dataTableRepository->getRowsContent($this);
    }

    public function getTotal(): int
    {
        // TODO: Implement getTotal() method.
    }

    public function getResponse(DataTableRowFactoryInterface $dataTableRowFactory): Response
    {
        // TODO: Implement getResponse() method.
    }


}
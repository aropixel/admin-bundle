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

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    public function getContext(): DataTableContext
    {
        return $this->context;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getRowsContent(): iterable
    {
        return $this->dataTableRepository->getRowsContent($this);
    }

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

        $records = array();
        $records["data"] = $this->getRows($dataTableRowFactory);
        $records["order"] = array();
        $records["draw"] = $this->context->getDraw();
        $records["recordsTotal"] = $count;
        $records["recordsFiltered"] = $count;


        $http_response = new Response(json_encode($records));
        $http_response->headers->set('Content-Type', 'application/json');
        return $http_response;
    }


}

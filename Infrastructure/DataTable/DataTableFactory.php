<?php

namespace Aropixel\AdminBundle\Infrastructure\DataTable;

use Aropixel\AdminBundle\Domain\DataTable\DataTableFactoryInterface;
use Aropixel\AdminBundle\Domain\DataTable\DataTableInterface;
use Aropixel\AdminBundle\Domain\DataTable\DataTableRepositoryInterface;
use Aropixel\AdminBundle\Infrastructure\DataTable\Repository\DoctrineDataTableRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class DataTableFactory implements DataTableFactoryInterface
{

    private DoctrineDataTableRepository $doctrineDataTableRepository;

    private ?DataTableRepositoryInterface $dataTableRepository = null;


    /**
     * @param DoctrineDataTableRepository $doctrineDataTableRepository
     */
    public function __construct(DoctrineDataTableRepository $doctrineDataTableRepository)
    {
        $this->doctrineDataTableRepository = $doctrineDataTableRepository;
    }


    public function setRepository(DataTableRepositoryInterface $dataTableRepository)
    {
        $this->dataTableRepository = $dataTableRepository;
    }

    public function create(string $className, array $columns): DataTableInterface
    {
        $context = $this->dataTableContextFactory->create();

        if (is_null($this->dataTableRepository)) {
            $this->setRepository($this->doctrineDataTableRepository);
        }

        return new DataTable($className, $columns, $context);
    }

}
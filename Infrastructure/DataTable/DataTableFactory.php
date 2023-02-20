<?php

namespace Aropixel\AdminBundle\Infrastructure\DataTable;

use Aropixel\AdminBundle\Domain\DataTable\DataTableContextFactoryInterface;
use Aropixel\AdminBundle\Domain\DataTable\DataTableFactoryInterface;
use Aropixel\AdminBundle\Domain\DataTable\DataTableInterface;
use Aropixel\AdminBundle\Domain\DataTable\DataTableRepositoryInterface;
use Aropixel\AdminBundle\Infrastructure\DataTable\Repository\Doctrine\DefaultDataTableRepository;

class DataTableFactory implements DataTableFactoryInterface
{

    private DataTableContextFactoryInterface $dataTableContextFactory;
    private DefaultDataTableRepository $doctrineDataTableRepository;

    private ?DataTableRepositoryInterface $dataTableRepository = null;


    /**
     * @param DataTableContextFactoryInterface $dataTableContextFactory
     * @param DefaultDataTableRepository $doctrineDataTableRepository
     */
    public function __construct(DataTableContextFactoryInterface $dataTableContextFactory, DefaultDataTableRepository $doctrineDataTableRepository)
    {
        $this->dataTableContextFactory = $dataTableContextFactory;
        $this->doctrineDataTableRepository = $doctrineDataTableRepository;
    }


    public function setRepository(DataTableRepositoryInterface $dataTableRepository) : DataTableFactoryInterface
    {
        $this->dataTableRepository = $dataTableRepository;
        return $this;
    }

    public function create(string $className, array $columns): DataTableInterface
    {
        $context = $this->dataTableContextFactory->create();

        if (is_null($this->dataTableRepository)) {
            $this->setRepository($this->doctrineDataTableRepository);
        }

        return new DataTable($className, $columns, $context, $this->dataTableRepository);
    }

}

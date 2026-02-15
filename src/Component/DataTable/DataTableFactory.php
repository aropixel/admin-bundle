<?php

namespace Aropixel\AdminBundle\Component\DataTable;

use Aropixel\AdminBundle\Component\DataTable\Context\DataTableContextFactoryInterface;
use Aropixel\AdminBundle\Component\DataTable\Repository\DataTableRepositoryInterface;
use Aropixel\AdminBundle\Component\DataTable\Repository\DefaultDataTableRepository;

class DataTableFactory implements DataTableFactoryInterface
{
    private ?DataTableRepositoryInterface $dataTableRepository = null;

    public function __construct(
        private readonly DataTableContextFactoryInterface $dataTableContextFactory,
        private readonly DefaultDataTableRepository $doctrineDataTableRepository
    ) {
    }

    public function setRepository(DataTableRepositoryInterface $dataTableRepository): DataTableFactoryInterface
    {
        $this->dataTableRepository = $dataTableRepository;

        return $this;
    }

    public function create(string $className, array $columns): DataTableInterface
    {
        $context = $this->dataTableContextFactory->create();

        if (null === $this->dataTableRepository) {
            $this->setRepository($this->doctrineDataTableRepository);
        }

        return new DataTable($className, $columns, $context, $this->dataTableRepository);
    }
}

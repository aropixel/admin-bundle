<?php

namespace Aropixel\AdminBundle\Component\DataTable;

use Aropixel\AdminBundle\Component\DataTable\Context\DataTableContextFactoryInterface;
use Aropixel\AdminBundle\Component\DataTable\Repository\DataTableRepositoryInterface;
use Aropixel\AdminBundle\Component\DataTable\Repository\DataTableRepository;
use Twig\Environment;

class DataTableFactory implements DataTableFactoryInterface
{
    private ?DataTableRepositoryInterface $dataTableRepository = null;

    public function __construct(
        private readonly DataTableContextFactoryInterface $dataTableContextFactory,
        private readonly DataTableRepository              $doctrineDataTableRepository,
        private readonly Environment                      $twig
    ) {
    }

    public function setRepository(DataTableRepositoryInterface $dataTableRepository): DataTableFactoryInterface
    {
        $this->dataTableRepository = $dataTableRepository;

        return $this;
    }

    public function create(string $className, array $columns = [], string $mode = DataTableInterface::MODE_XHR): DataTableInterface
    {
        $context = $this->dataTableContextFactory->create();

        if (null === $this->dataTableRepository) {
            $this->setRepository($this->doctrineDataTableRepository);
        }

        $dataTable = new DataTable($className, $columns, $context, $this->dataTableRepository, $this->twig);
        $dataTable->setMode($mode);

        return $dataTable;
    }
}

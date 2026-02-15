<?php

namespace Aropixel\AdminBundle\Controller\File;

use Aropixel\AdminBundle\Component\DataTable\Column\DataTableColumn;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactoryInterface;
use Aropixel\AdminBundle\Component\Media\Image\Library\DataTable\DataTableRowFactory;
use Aropixel\AdminBundle\Component\Media\Resolver\ClassNameResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AjaxAction extends AbstractController
{
    public function __construct(
        private readonly ClassNameResolverInterface $classNameResolver,
        private readonly DataTableFactoryInterface $dataTableFactory,
        private readonly DataTableRowFactory $dataTableRowFactory
    ) {
    }

    /**
     * Lists all File entities.
     */
    public function __invoke(): Response
    {
        $dataTable = $this->dataTableFactory->create($this->classNameResolver->getFileclassName(), [
            new DataTableColumn('', '', 'width:50px;'),
            new DataTableColumn('Titre', 'title'),
            new DataTableColumn('Date', 'createdAt'),
            new DataTableColumn('Fichier', '', 'width:200px;'),
            new DataTableColumn('', ''),
        ]);

        return $dataTable->getResponse($this->dataTableRowFactory);
    }
}

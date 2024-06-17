<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Domain\DataTable\DataTableColumn;
use Aropixel\AdminBundle\Domain\DataTable\DataTableFactoryInterface;
use Aropixel\AdminBundle\Domain\Media\Image\Library\DataTable\DataTableRowFactory;
use Aropixel\AdminBundle\Domain\Media\Resolver\ClassNameResolverInterface;
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
     * Lists all Image entities.
     */
    public function __invoke(): Response
    {
        $dataTable = $this->dataTableFactory->create($this->classNameResolver->getImageClassName(), [
            new DataTableColumn('', '', 'width:50px;'),
            new DataTableColumn('Aperçu', '', 'width:100px;'),
            new DataTableColumn('Titre', 'title'),
            new DataTableColumn('Date', 'createdAt'),
            new DataTableColumn('Fichier', '', 'width:200px;'),
            new DataTableColumn('', ''),
        ]);

        return $dataTable->getResponse($this->dataTableRowFactory);
    }
}

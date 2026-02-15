<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Component\DataTable\Column\DataTableColumn;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactoryInterface;
use Aropixel\AdminBundle\Component\Media\File\Library\DataTable\DataTableRepository;
use Aropixel\AdminBundle\Component\Media\File\Library\DataTable\DataTableRowFactory;
use Aropixel\AdminBundle\Component\Media\Resolver\ClassNameResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AjaxCategoryAction extends AbstractController
{
    public function __construct(
        private readonly ClassNameResolverInterface $classNameResolver,
        private readonly DataTableFactoryInterface $dataTableFactory,
        private readonly DataTableRowFactory $dataTableRowFactory,
        private readonly DataTableRepository $fileDataTableRepository
    ) {
    }

    /**
     * Lists all File entities.
     */
    public function __invoke(string $category): Response
    {
        $dataTable = $this->dataTableFactory
            ->setRepository($this->fileDataTableRepository)
            ->create($this->classNameResolver->getFileClassName(), [
                new DataTableColumn('', '', 'width:50px;'),
                new DataTableColumn('Aperçu', '', 'width:100px;'),
                new DataTableColumn('Titre', 'title'),
                new DataTableColumn('Date', 'createdAt'),
                new DataTableColumn('Fichier', '', 'width:200px;'),
                new DataTableColumn('', ''),
            ])
        ;

        $dataTable->getContext()->addParameters(['category' => $category]);

        return $dataTable->getResponse($this->dataTableRowFactory);
    }
}

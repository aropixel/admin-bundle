<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Domain\DataTable\DataTableColumn;
use Aropixel\AdminBundle\Domain\DataTable\DataTableFactoryInterface;
use Aropixel\AdminBundle\Domain\Media\Image\Library\DataTable\DataTableRowFactory;
use Aropixel\AdminBundle\Domain\Media\Resolver\ClassNameResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AjaxAction extends AbstractController
{

    private ClassNameResolverInterface $classNameResolver;
    private DataTableFactoryInterface $dataTableFactory;
    private DataTableRowFactory $dataTableRowFactory;

    /**
     * @param ClassNameResolverInterface $classNameResolver
     * @param DataTableFactoryInterface $dataTableFactory
     * @param DataTableRowFactory $dataTableRowFactory
     */
    public function __construct(ClassNameResolverInterface $classNameResolver, DataTableFactoryInterface $dataTableFactory, DataTableRowFactory $dataTableRowFactory)
    {
        $this->classNameResolver = $classNameResolver;
        $this->dataTableFactory = $dataTableFactory;
        $this->dataTableRowFactory = $dataTableRowFactory;
    }


    /**
     * Lists all File entities.
     */
    public function __invoke() : Response
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

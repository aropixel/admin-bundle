<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Domain\DataTable\DataTableColumn;
use Aropixel\AdminBundle\Domain\DataTable\DataTableFactoryInterface;
use Aropixel\AdminBundle\Domain\Media\Image\Library\DataTable\DataTableRowFactory;
use Aropixel\AdminBundle\Domain\Media\Resolver\ClassNameResolverInterface;
use Aropixel\AdminBundle\Infrastructure\Media\Image\Library\DataTable\DataTableRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;


class AjaxCategoryAction extends AbstractController
{
    private ClassNameResolverInterface $classNameResolver;
    private DataTableFactoryInterface $dataTableFactory;
    private DataTableRowFactory $dataTableRowFactory;
    private DataTableRepository $imageDataTableRepository;

    /**
     * @param ClassNameResolverInterface $classNameResolver
     * @param DataTableFactoryInterface $dataTableFactory
     * @param DataTableRowFactory $dataTableRowFactory
     * @param DataTableRepository $imageDataTableRepository
     */
    public function __construct(ClassNameResolverInterface $classNameResolver, DataTableFactoryInterface $dataTableFactory, DataTableRowFactory $dataTableRowFactory, DataTableRepository $imageDataTableRepository)
    {
        $this->classNameResolver = $classNameResolver;
        $this->dataTableFactory = $dataTableFactory;
        $this->dataTableRowFactory = $dataTableRowFactory;
        $this->imageDataTableRepository = $imageDataTableRepository;
    }


    /**
     * Lists all Image entities.
     */
    public function __invoke(string $category) : Response
    {

        $dataTable = $this->dataTableFactory
            ->setRepository($this->imageDataTableRepository)
            ->create($this->classNameResolver->getImageClassName(), [
                new DataTableColumn('', '', 'width:50px;'),
                new DataTableColumn('Aperçu', '', 'width:100px;'),
                new DataTableColumn('Titre', 'title'),
                new DataTableColumn('Date', 'createdAt'),
                new DataTableColumn('Fichier', '', 'width:200px;'),
                new DataTableColumn('', ''),
            ]);


        $dataTable->getContext()->addParameters(['category' => $category]);

        return $dataTable->getResponse($this->dataTableRowFactory);

    }


}

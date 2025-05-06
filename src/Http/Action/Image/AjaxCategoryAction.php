<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Domain\DataTable\DataTableColumn;
use Aropixel\AdminBundle\Domain\DataTable\DataTableFactoryInterface;
use Aropixel\AdminBundle\Domain\Media\Image\Library\DataTable\DataTableRowFactory;
use Aropixel\AdminBundle\Domain\Media\Resolver\ClassNameResolverInterface;
use Aropixel\AdminBundle\Infrastructure\Media\Image\Library\DataTable\DataTableRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxCategoryAction extends AbstractController
{
    public function __construct(
        private readonly ClassNameResolverInterface $classNameResolver,
        private readonly DataTableFactoryInterface $dataTableFactory,
        private readonly DataTableRowFactory $dataTableRowFactory,
        private readonly DataTableRepository $imageDataTableRepository
    ) {
    }

    /**
     * Lists all Image entities.
     */
    public function __invoke(Request $request): Response
    {
        $category = $request->get('category');
        $dataTable = $this->dataTableFactory
            ->setRepository($this->imageDataTableRepository)
            ->create($this->classNameResolver->getImageClassName(), [
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

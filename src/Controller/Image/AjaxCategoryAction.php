<?php

namespace Aropixel\AdminBundle\Controller\Image;

use Aropixel\AdminBundle\Component\DataTable\Column\DataTableColumn;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactoryInterface;
use Aropixel\AdminBundle\Component\Media\Image\Library\DataTable\DataTableRowFactory;
use Aropixel\AdminBundle\Component\Media\Resolver\ClassNameResolverInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxCategoryAction extends AbstractController
{
    public function __construct(
        private readonly ClassNameResolverInterface $classNameResolver,
        private readonly DataTableFactoryInterface $dataTableFactory,
        private readonly DataTableRowFactory $dataTableRowFactory,
    ) {
    }

    /**
     * Lists all Image entities.
     */
    public function __invoke(Request $request): Response
    {
        $category = $request->query->get('category');
        $dataTable = $this->dataTableFactory
            ->create($this->classNameResolver->getImageClassName(), [
                new DataTableColumn('', '', 'width:50px;'),
                new DataTableColumn('Aperçu', '', 'width:100px;'),
                new DataTableColumn('Titre', 'title'),
                new DataTableColumn('Date', 'createdAt'),
                new DataTableColumn('Fichier', '', 'width:200px;'),
                new DataTableColumn('', ''),
            ])
            ->filter(function(QueryBuilder $qb) use ($category) {
                $qb->andWhere('i.category = :category')->setParameter('category', $category);
            })
            ->searchIn(['title'])
        ;

        return $dataTable->getResponse($this->dataTableRowFactory);
    }
}

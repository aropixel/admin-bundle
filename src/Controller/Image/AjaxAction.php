<?php

namespace Aropixel\AdminBundle\Controller\Image;

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
     * Lists all Image entities.
     */
    public function __invoke(): Response
    {
        return $this->dataTableFactory->create($this->classNameResolver->getImageClassName())
            ->setColumns([
                ['label' => '', 'orderBy' => '', 'style' => 'width:50px;'],
                ['label' => 'Aperçu', 'orderBy' => '', 'style' => 'width:100px;'],
                ['label' => 'Titre', 'orderBy' => 'title'],
                ['label' => 'Date', 'orderBy' => 'createdAt'],
                ['label' => 'Fichier', 'orderBy' => '', 'style' => 'width:200px;'],
                ['label' => '', 'orderBy' => ''],
            ])
            ->searchIn(['title'])
            ->renderJson($this->dataTableRowFactory)
        ;
    }
}

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
                ['label' => '', 'field' => '', 'style' => 'width:50px;'],
                ['label' => 'Aperçu', 'field' => '', 'style' => 'width:100px;'],
                ['label' => 'Titre', 'field' => 'title'],
                ['label' => 'Date', 'field' => 'createdAt'],
                ['label' => 'Fichier', 'field' => '', 'style' => 'width:200px;'],
                ['label' => '', 'field' => ''],
            ])
            ->searchIn(['title'])
            ->renderJson($this->dataTableRowFactory)
        ;
    }
}

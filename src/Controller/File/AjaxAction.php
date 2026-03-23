<?php

namespace Aropixel\AdminBundle\Controller\File;

use Aropixel\AdminBundle\Component\DataTable\DataTableFactoryInterface;
use Aropixel\AdminBundle\Component\Media\File\Library\DataTable\DataTableRowFactory;
use Aropixel\AdminBundle\Component\Media\Resolver\ClassNameResolverInterface;
use Aropixel\AdminBundle\Entity\File;
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
        return $this->dataTableFactory->create($this->classNameResolver->getFileclassName())
            ->setColumns([
                ['label' => '', 'orderBy' => '', 'style' => 'width:50px;'],
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

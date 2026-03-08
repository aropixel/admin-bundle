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
                ['label' => '', 'field' => '', 'style' => 'width:50px;'],
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

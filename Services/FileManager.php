<?php

namespace Aropixel\AdminBundle\Services;

use Aropixel\AdminBundle\Entity\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class FileManager
{

    public function __construct(
        private readonly Container $container
    ){}

    public function getFileClassName()
    {
        $entities = $this->container->getParameter('aropixel_admin.entities');
        return $entities[FileInterface::class];
    }


}

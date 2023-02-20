<?php
// src/Aropixel/AdminBundle/Services/Datatabler.php
namespace Aropixel\AdminBundle\Services;

use Aropixel\AdminBundle\Entity\ImageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;


class ImageManager
{

    public function __construct(
        private readonly Container $container,
    ){}


    public function getImageClassName()
    {
        $entities = $this->container->getParameter('aropixel_admin.entities');
        return $entities[ImageInterface::class];
    }






}

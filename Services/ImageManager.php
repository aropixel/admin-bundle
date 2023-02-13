<?php
// src/Aropixel/AdminBundle/Services/Datatabler.php
namespace Aropixel\AdminBundle\Services;

use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Entity\ImageInterface;
use Aropixel\AdminBundle\Resolver\PathResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\PropertyAccess\PropertyAccess;


class ImageManager
{

    public function __construct(
        private readonly Container $container,
        private readonly PathResolverInterface $pathResolver,
        private readonly FilterService $filterService
    ){}


    /**
     * Récupère les filtres de crop attribué à aucune entité
     */
    public function getOrphanFilters() : array
    {
        return $this->container->getParameter('aropixel_admin.editor_filter_sets');
    }

    public function getImageClassName()
    {
        $entities = $this->container->getParameter('aropixel_admin.entities');
        return $entities[ImageInterface::class];
    }




    /**
     * Applique le crop en BDD et sur le fichier
     */
    public function editorResize(Image $image, $width, string $filter = null) : string
    {

        // Si aucun filtre mais une largeur
        // on récupère le filtre de largeur préconstruit par le bundle
        if ((is_null($filter) || !strlen($filter)) && $width) {
            $filter = 'editor_'.$width;
        }

        if (!is_null($filter)) {
            $resourcePath = $this->filterService->getUrlOfFilteredImage($image->getWebPath(), $filter);
        }
        else {

            $size = getimagesize($this->pathResolver->getAbsolutePath(Image::UPLOAD_DIR, $image->getFilename()));

            // Runtime configuration
            $runtimeConfig = [
                'relative_resize' => [
                    'widen' => $size[0]
                ],
            ];

            $resourcePath = $this->filterService->getUrlOfFilteredImageWithRuntimeFilters(
                $image->getWebPath(),
                'auto',
                $runtimeConfig
            );

        }

        return $resourcePath;

    }




}

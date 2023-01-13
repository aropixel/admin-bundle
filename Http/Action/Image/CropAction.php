<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Services\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class CropAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageManager $imageManager
    ){}

    /**
     * Crop an Image.
     */
    public function __invoke(Request $request, ImageManager $imageManager)
    {

        $route_name = $request->get('route');

        $image_id = $request->get('image_id');
        $imageClassName = $this->imageManager->getImageClassName();
        $image = $this->entityManager->getRepository($imageClassName)->find($image_id);

        $filters = $imageManager->getCropFilters($route_name, $image);

        return $this->render('@AropixelAdmin/Image/Modals/crop.html.twig', [
            'filters' => $filters, 'image' => $image
        ]);

    }


}
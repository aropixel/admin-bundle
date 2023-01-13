<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Services\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class SaveCropAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageManager $imageManager
    ){}

    /**
     * Save crop info of an Image.
     */
    public function __invoke(Request $request, ImageManager $imageManager) : Response
    {

        $image_id = $request->get('image_id');
        $filter = $request->get('filter');
        $crop_infos = $request->get('crop_info');

        // Charge l'image à cropper
        $imageClassName = $this->imageManager->getImageClassName();
        $image = $this->entityManager->getRepository($imageClassName)->find($image_id);

        // Pour chaque filtre passé, on recrope l'image chargée
        foreach ($crop_infos as $filter => $crop_info) {

            $imageManager->saveCrop($image, $filter, $crop_info);

        }

        return new Response('Done', Response::HTTP_OK);

    }

}
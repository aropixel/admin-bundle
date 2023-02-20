<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Services\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class TitleAction extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ImageManager $imageManager;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ImageManager $imageManager
     */
    public function __construct(EntityManagerInterface $entityManager, ImageManager $imageManager)
    {
        $this->entityManager = $entityManager;
        $this->imageManager = $imageManager;
    }

    /**
     * Add a title.
     */
    public function __invoke(Request $request) : Response
    {

        $image_id = $request->get('pk');
        $title = $request->get('value');

        $em = $this->entityManager;

        $imageClassName = $this->imageManager->getImageClassName();
        $image = $em->getRepository($imageClassName)->find($image_id);
        $image->setTitre($title);
        $em->flush();

        return new Response('Done', Response::HTTP_OK);

    }



}

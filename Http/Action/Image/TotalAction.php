<?php

namespace Aropixel\AdminBundle\Http\Action\Image;


use Aropixel\AdminBundle\Services\ImageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TotalAction extends AbstractController
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
     * Count Image entities.
     */
    public function __invoke(Request $request) : Response
    {

        $category = $request->get('category');

        $imageClassName = $this->imageManager->getImageClassName();
        $repository = $this->entityManager->getRepository($imageClassName);
        $nbs = $repository->count($category);

        return new Response($nbs, Response::HTTP_OK);

    }


}

<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Domain\Media\Image\Library\Repository\ImageRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class TitleAction extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ImageRepositoryInterface $imageRepository;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ImageRepositoryInterface $imageRepository
     */
    public function __construct(EntityManagerInterface $entityManager, ImageRepositoryInterface $imageRepository)
    {
        $this->entityManager = $entityManager;
        $this->imageRepository = $imageRepository;
    }


    /**
     * Add a title.
     */
    public function __invoke(Request $request) : Response
    {

        $image_id = $request->get('pk');
        $title = $request->get('value');

        $image = $this->imageRepository->find($image_id);
        $image->setTitre($title);
        $this->entityManager->flush();

        return new Response('Done', Response::HTTP_OK);

    }



}

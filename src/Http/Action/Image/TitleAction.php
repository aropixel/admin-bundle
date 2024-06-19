<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Domain\Media\Image\Library\Repository\ImageRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TitleAction extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageRepositoryInterface $imageRepository
    ) {
    }

    /**
     * Add a title.
     */
    public function __invoke(Request $request): Response
    {
        $image_id = $request->get('pk');
        $title = $request->get('value');

        $image = $this->imageRepository->find($image_id);
        $image->setTitle($title);
        $this->entityManager->flush();

        return new Response('Done', Response::HTTP_OK);
    }
}

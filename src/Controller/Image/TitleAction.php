<?php

namespace Aropixel\AdminBundle\Controller\Image;

use Aropixel\AdminBundle\Repository\ImageRepositoryInterface;
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
        $payload = $request->getPayload();
        $image_id = $payload->get('pk');
        $title = $payload->get('value');

        $image = $image_id ? $this->imageRepository->find($image_id) : null;

        if (!$image) {
            return new Response('KO', Response::HTTP_NOT_FOUND);
        }

        $image->setTitle((string) $title);
        $this->entityManager->flush();

        return new Response('Done', Response::HTTP_OK);
    }
}

<?php

namespace Aropixel\AdminBundle\Controller\File;

use Aropixel\AdminBundle\Repository\FileRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TitleAction extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileRepositoryInterface $fileRepository
    ) {
    }

    /**
     * Add a title.
     */
    public function __invoke(Request $request): Response
    {
        $payload = $request->getPayload();
        $file_id = $payload->get('pk');
        $title = $payload->get('value');

        $file = $file_id ? $this->fileRepository->find($file_id) : null;

        if (!$file) {
            return new Response('KO', Response::HTTP_NOT_FOUND);
        }

        $file->setTitle((string) $title);
        $this->entityManager->flush();

        return new Response('Done', Response::HTTP_OK);
    }
}

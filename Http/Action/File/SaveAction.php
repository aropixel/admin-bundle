<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Domain\Media\File\Library\Repository\FileRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SaveAction extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileRepositoryInterface $fileRepository
    ) {
    }

    /**
     * Save crop info of an Image.
     */
    public function __invoke(Request $request): Response
    {
        $entity_id = $request->request->get('id');
        $title = $request->request->get('title');

        $file = $this->fileRepository->find($entity_id);
        if ($file) {
            $file->setTitle($title);
            $this->entityManager->flush();
        }

        return new Response($title, Response::HTTP_OK);
    }
}

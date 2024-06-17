<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Domain\Media\File\Library\Repository\FileRepositoryInterface;
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
        $file_id = $request->get('pk');
        $title = $request->get('value');

        $file = $this->fileRepository->find($file_id);
        $file->setTitle($title);
        $this->entityManager->flush();

        return new Response('Done', Response::HTTP_OK);
    }
}

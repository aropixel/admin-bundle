<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Domain\Media\File\Library\Repository\FileRepositoryInterface;
use Aropixel\AdminBundle\Services\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class TitleAction extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private FileRepositoryInterface $fileRepository;

    /**
     * @param EntityManagerInterface $entityManager
     * @param FileRepositoryInterface $fileRepository
     */
    public function __construct(EntityManagerInterface $entityManager, FileRepositoryInterface $fileRepository)
    {
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
    }


    /**
     * Add a title.
     */
    public function __invoke(Request $request) : Response
    {
        $file_id = $request->get('pk');
        $title = $request->get('value');

        $file = $this->fileRepository->find($file_id);
        $file->setTitle($title);
        $this->entityManager->flush();

        return new Response('Done', Response::HTTP_OK);

    }



}

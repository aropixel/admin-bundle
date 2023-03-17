<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Domain\Media\File\Library\Repository\FileRepositoryInterface;
use Aropixel\AdminBundle\Services\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class SaveAction extends AbstractController
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
     * Save crop info of an Image.
     */
    public function __invoke(Request $request) : Response
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

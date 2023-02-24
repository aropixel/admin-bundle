<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Services\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class SaveAction extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private FileManager $fileManager;

    /**
     * @param EntityManagerInterface $entityManager
     * @param FileManager $fileManager
     */
    public function __construct(EntityManagerInterface $entityManager, FileManager $fileManager)
    {
        $this->entityManager = $entityManager;
        $this->fileManager = $fileManager;
    }


    /**
     * Save crop info of an Image.
     */
    public function __invoke(Request $request) : Response
    {

        $entity_id = $request->request->get('id');
        $title = $request->request->get('title');
        $em = $this->entityManager;

        $fileClassName = $this->fileManager->getFileClassName();
        $file = $em->getRepository($fileClassName)->find($entity_id);

        if ($file) {

            $file->setTitle($title);
            $em->flush();

        }

        return new Response($title, Response::HTTP_OK);

    }


}

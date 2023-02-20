<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Services\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class TitleAction extends AbstractController
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
     * Add a title.
     */
    public function __invoke(Request $request) : Response
    {

        $file_id = $request->get('pk');
        $title = $request->get('value');

        $em = $this->entityManager;

        $fileClassName = $this->fileManager->getFileClassName();
        $image = $em->getRepository($fileClassName)->find($file_id);
        $image->setTitre($title);
        $em->flush();

        return new Response('Done', Response::HTTP_OK);

    }



}

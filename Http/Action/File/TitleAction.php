<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Services\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class TitleAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileManager $fileManager
    ){}

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
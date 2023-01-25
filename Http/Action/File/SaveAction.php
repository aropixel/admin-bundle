<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Services\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class SaveAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FileManager $fileManager
    ){}

    /**
     * Save crop info of an Image.
     */
    public function __invoke(Request $request) : Response
    {

        $entity_id = $request->request->get('id');
        $titre = $request->request->get('titre');
        $em = $this->entityManager;

        $fileClassName = $this->fileManager->getFileClassName();
        $file = $em->getRepository($fileClassName)->find($entity_id);

        if ($file) {

            $file->setTitre($titre);
            $em->flush();

        }

        return new Response($titre, Response::HTTP_OK);

    }


}

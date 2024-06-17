<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Entity\AttachedFile;
use Aropixel\AdminBundle\Entity\File;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class DeleteAction extends AbstractController
{
    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * Delete a file
     */
    public function __invoke(Request $request) : Response
    {

        $file_id = $request->get('file_id');
        $libraryClass = $request->get('category');

        $em = $this->entityManager;
        $file = $em->getRepository(File::class)->find($file_id);

        if ($file) {

            $libraryEntity = new \ReflectionClass($libraryClass);
            if ($libraryEntity instanceof AttachedFile) {

                $attachedFiles = $em->getRepository($libraryClass)->findBy(['file' => $file]);

                if (count($attachedFiles)) {

                    foreach ($attachedFiles as $attachedFile) {
                        $em->remove($attachedFile);
                    }
                    $em->flush();

                }

            }

            $em->remove($file);
            $em->flush();

        }

        return new Response('OK', Response::HTTP_OK);

    }


}

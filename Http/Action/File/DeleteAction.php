<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Entity\AttachedFile;
use Aropixel\AdminBundle\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DeleteAction extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Delete a file.
     */
    public function __invoke(Request $request): Response
    {
        $file_id = $request->get('file_id');
        $libraryClass = $request->get('category');

        $em = $this->entityManager;
        $file = $em->getRepository(File::class)->find($file_id);

        if ($file) {
            $libraryEntity = new \ReflectionClass($libraryClass);
            if ($libraryEntity instanceof AttachedFile) {
                $attachedFiles = $em->getRepository($libraryClass)->findBy(['file' => $file]);

                if (\count($attachedFiles)) {
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

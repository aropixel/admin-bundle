<?php

namespace Aropixel\AdminBundle\Controller\File;

use Aropixel\AdminBundle\Entity\File;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
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
        $file_id = $request->getPayload()->get('file_id');

        if (!$file_id) {
            return new Response('KO', Response::HTTP_OK);
        }

        $em = $this->entityManager;
        $file = $em->getRepository(File::class)->find($file_id);

        if ($file) {
            try {
                $em->remove($file);
                $em->flush();
            } catch (ForeignKeyConstraintViolationException) {
                return new Response('FOREIGN_KEY', Response::HTTP_OK);
            } catch (\Exception) {
                return new Response('KO', Response::HTTP_OK);
            }
        }

        return new Response('OK', Response::HTTP_OK);
    }
}

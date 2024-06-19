<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Domain\Media\Image\Library\Repository\ImageRepositoryInterface;
use Aropixel\AdminBundle\Entity\AttachedImage;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DeleteAction extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageRepositoryInterface $imageRepository
    ) {
    }

    /**
     * Delete an Image.
     */
    public function __invoke(Request $request): Response
    {
        $image_id = $request->get('image_id');
        $libraryClass = $request->get('category');
        $image = $this->imageRepository->find($image_id);

        if ($image) {
            try {
                $libraryEntity = new \ReflectionClass($libraryClass);
                if ($libraryEntity instanceof AttachedImage) {
                    $attachedImages = $this->entityManager->getRepository($libraryClass)->findBy(['image' => $image]);
                    if (\count($attachedImages)) {
                        foreach ($attachedImages as $attachedImage) {
                            $this->entityManager->remove($attachedImage);
                        }
                        $this->entityManager->flush();
                    }
                }

                $this->entityManager->remove($image);
                $this->entityManager->flush();
            } catch (ForeignKeyConstraintViolationException) {
                return new Response('FOREIGN_KEY', Response::HTTP_OK);
            } catch (\Exception) {
                return new Response('KO', Response::HTTP_OK);
            }
        }

        return new Response('OK', Response::HTTP_OK);
    }
}

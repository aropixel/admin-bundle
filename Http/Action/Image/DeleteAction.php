<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Entity\AttachImage;
use Aropixel\AdminBundle\Services\ImageManager;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class DeleteAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageManager $imageManager
    ){}


    /**
     * Delete an Image.
     */
    public function __invoke(Request $request) : Response
    {
        $image_id = $request->get('image_id');
        $libraryClass = $request->get('category');

        $em = $this->entityManager;
        $imageClassName = $this->imageManager->getImageClassName();
        $image = $em->getRepository($imageClassName)->find($image_id);

        if ($image) {

            try {

                $libraryEntity = new \ReflectionClass($libraryClass);
                if ($libraryEntity instanceof AttachImage) {

                    $attachedImages = $em->getRepository($libraryClass)->findBy(['image' => $image]);
                    if (count($attachedImages)) {

                        foreach ($attachedImages as $attachedImage) {
                            $em->remove($attachedImage);
                        }
                        $em->flush();

                    }

                }

                $em->remove($image);
                $em->flush();

            }

            catch(ForeignKeyConstraintViolationException $e) {
                return new Response('FOREIGN_KEY', Response::HTTP_OK);
            }

            catch(\Exception $e) {
                return new Response('KO', Response::HTTP_OK);
            }

        }

        return new Response('OK', Response::HTTP_OK);

    }


}
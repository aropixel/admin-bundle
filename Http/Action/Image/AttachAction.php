<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Aropixel\AdminBundle\Services\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AttachAction extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ImageManager $imageManager;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ImageManager $imageManager
     */
    public function __construct(EntityManagerInterface $entityManager, ImageManager $imageManager)
    {
        $this->entityManager = $entityManager;
        $this->imageManager = $imageManager;
    }


    /**
     * Attach an Image.
     */
    public function __invoke(Request $request) : Response
    {

        // Selected images
        $images = $request->get('images');

        // Class name to use if data type is entity
        $attachClass = $request->get('attach_class');

        // The property to store file name if needed
        $attachValue = $request->get('attach_value');

        // Id
        $attachId = $request->get('attach_id');

        // Crops
        $cropsSlugs = $request->get('crops_slugs', '');
        $cropsLabels = $request->get('crops_labels', '');
        $em = $this->entityManager;

        $options = ['crops' => []];

        if (strlen($cropsSlugs)) {

            $i = 0;
            $cropsSlugs = explode(';', $cropsSlugs);
            $cropsLabels = explode(';', $cropsLabels);
            foreach ($cropsSlugs as $slug) {
                $options['crops'][$slug] = $cropsLabels[$i++];
            }

        }

        $data = null;
        if ($attachValue) {

            $options['data_class'] = $attachClass;

            if ($attachValue) {
                $options['data_value'] = $attachValue;
            }

        }

        else {

            $options['data_class'] = $attachClass;
            $data = new $attachClass();

            if ($attachId) {
                $data = $em->getRepository($attachClass)->find($attachId);
            }

        }


        $html = '';
        foreach ($images as $image_id) {

            $imageClassName = $this->imageManager->getImageClassName();
            $image = $em->getRepository($imageClassName)->find($image_id);

            // If attachValue is given, we just pass the filename
            if ($attachValue) {
                $data = new $attachClass();
                $propertyAccessor = PropertyAccess::createPropertyAccessor();
                $propertyAccessor->setValue($data, $attachValue, $image->getFilename());
            }
            // Otherwise, datatype is entity, we give the image to the entity
            else {
                $data->setImage($image);
            }

            $form = $this->createForm(ImageType::class, $data, $options);

            $html.= $this->renderView('@AropixelAdmin/Image/Widget/image.html.twig', [
                'form' => $form->createView()
            ]);

        }

        return new Response($html, Response::HTTP_OK);

    }


}

<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Component\Media\Image\Library\Repository\ImageRepositoryInterface;
use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AttachAction extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private readonly ImageRepositoryInterface $imageRepository
    ) {
    }

    /**
     * Attach an Image.
     */
    public function __invoke(Request $request): Response
    {
        $json = json_decode($request->getContent());

        // Selected images
        $images = $json->images;

        $multiple = $json->multiple;

        // Class name to use if data type is entity
        $attachClass = $json->attach_class;

        // The property to store file name if needed
        $attachValue = $json->attach_value;

        // Id
        $attachId = $json->attach_id;

        // Crops
        $cropsSlugs = $json->crops_slugs ?? '';
        $cropsLabels = $json->crops_labels ?? '';
        $em = $this->entityManager;

        $options = ['crops' => []];

        if (mb_strlen((string) $cropsSlugs)) {
            $i = 0;
            $cropsSlugs = explode(';', (string) $cropsSlugs);
            $cropsLabels = explode(';', (string) $cropsLabels);
            foreach ($cropsSlugs as $slug) {
                $options['crops'][$slug] = $cropsLabels[$i++];
            }
        }

        $data = null;
        if ($attachValue) {
            $options['data_class'] = $attachClass;
            $options['data_value'] = $attachValue;
        } else {
            $options['data_class'] = $attachClass;
            $data = new $attachClass();

            if ($attachId) {
                $data = $em->getRepository($attachClass)->find($attachId);
            }
        }

        $html = '';
        if (!$multiple && !empty($images)) {
            $image_id = $images[0];

            $html = $this->getHtml($image_id, $attachValue, $attachClass, $data, $options, $html);
        } else {
            foreach ($images as $image_id) {
                $html = $this->getHtml($image_id, $attachValue, $attachClass, $data, $options, $html);
            }
        }

        return new Response($html, Response::HTTP_OK);
    }

    /**
     * @param array<mixed> $options
     */
    private function getHtml(mixed $image_id, string $attachValue, string $attachClass, mixed $data, array $options, string $html): string
    {
        $image = $this->imageRepository->find($image_id);

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

        $html .= $this->renderView('@AropixelAdmin/Image/Widget/image.html.twig', [
            'form' => $form->createView(),
        ]);

        return $html;
    }
}

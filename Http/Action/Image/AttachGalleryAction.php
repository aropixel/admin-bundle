<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Services\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class AttachGalleryAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageManager $imageManager
    ){}

    /**
     * Attach an Image.
     */
    public function __invoke(Request $request) : Response
    {
        $entity_id = $request->get('id');
        $routeName = $request->get('route');
        $images = $request->get('images');
        $multiple = $request->get('multiple');
        $category = $request->get('category');
        $position = $request->get('position');
        $t_entity = explode('\\', $category);

        $em = $this->entityManager;

        $entity_name = array_pop($t_entity);
        array_pop($t_entity);
        $short_namespace = implode('', $t_entity);

        $entity = $em->getRepository($short_namespace.':'.$entity_name)->find($entity_id);

        $html = '';
        foreach ($images as $image_id) {

            $imageClassName = $this->imageManager->getImageClassName();
            $image = $em->getRepository($imageClassName)->find($image_id);

            $html.= $this->renderView('@AropixelAdmin/Image/Widget/gallery.html.twig', array(
                'id'        => $entity_id,
                'category'  => $category,
                'image'     => $image,
                'entity'    => $entity,
                'position'  => $position,
                'routeName' => $routeName,
                'multiple'  => $multiple,
            ));

            $position++;
        }

        return new Response($html, Response::HTTP_OK);

    }


}
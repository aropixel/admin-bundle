<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Domain\Media\Image\Library\Repository\ImageRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class AttachGalleryAction extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ImageRepositoryInterface $imageRepository;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ImageRepositoryInterface $imageRepository
     */
    public function __construct(EntityManagerInterface $entityManager, ImageRepositoryInterface $imageRepository)
    {
        $this->entityManager = $entityManager;
        $this->imageRepository = $imageRepository;
    }


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

            $image = $this->imageRepository->find($image_id);
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

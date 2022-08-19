<?php

namespace Aropixel\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


/**
 * Image controller.
 *
 * @Route("/gallery")
 */
class GalleryController extends AbstractController
{

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Attach an Image.
     *
     * @Route("/attach", name="gallery_attach", options={"expose"=true}, methods={"POST"})
     */
    public function attachAction(Request $request)
    {
        //
        $entity_id = $request->get('id');
        $routeName = $request->get('route');
        $images = $request->get('images');
        $multiple = $request->get('multiple');
        $category = $request->get('category');
        $position = $request->get('position');


        $html = '';
        foreach ($images as $image_id) {

            $image = $this->entityManager->getRepository('AropixelAdminBundle:Image')->find($image_id);

            $html.= $this->renderView('AropixelGalleryBundle::image.html.twig', array(
                'id'        => $entity_id,
                'category'  => $category,
                'image'     => $image,
                'entity'    => false,
                'routeName' => $routeName,
                'multiple'  => $multiple,
                'position'  => $position,
            ));
            $position++;

        }


        //
        return new Response($html, Response::HTTP_OK);
    }



    /**
     * Attach a video.
     *
     * @Route("/attach/video", name="gallery_video", options={"expose"=true}, methods={"POST"})
     */
    public function videoAction(Request $request)
    {
        //
        $entity_id = $request->get('id');
        $routeName = $request->get('route');
        $iframe = $request->get('iframe');
        $multiple = $request->get('multiple');
        $category = $request->get('category');
        $position = $request->get('position');


        $html= $this->renderView('AropixelGalleryBundle::video.html.twig', array(
            'id'        => $entity_id,
            'category'  => $category,
            'iframe'     => $iframe,
            'entity'    => false,
            'routeName' => $routeName,
            'multiple'  => $multiple,
            'position'  => $position,
        ));


        //
        return new Response($html, Response::HTTP_OK);
    }

}

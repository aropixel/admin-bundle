<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Services\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class AttachEditorAction
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageManager $imageManager
    ){}

    /**
     * Attach an Image.
     */
    public function __invoke(Request $request, ImageManager $imageManager) : Response
    {
        $html = "";

        $images_id = $request->get('images', []);
        $width = $request->get('width', 300);
        $decoupe = $request->get('filter', null);
        $alt = $request->get('alt', '');

        $em = $this->entityManager;

        if ($width=='customfilter') {
            $width = null;
        }

        if ($width=='auto') {
            $width = null;
            $decoupe = null;
        }

        if (count($images_id)) {

            if (strlen($alt)) $alt = ' alt="'.$alt.'"';

            foreach ($images_id as $image_id) {

                $imageClassName = $this->imageManager->getImageClassName();
                $image = $em->getRepository($imageClassName)->find($image_id);
                $url = $imageManager->editorResize($image, $width, $decoupe);

                $class = "";
                $widthTag = ' width="'.$width.'"';
                if ($width=='100pc') {
                    $class = ' class="img-fluid img-responsive"';
                    $widthTag = '';
                }
                if (is_null($width)) {
                    $widthTag = '';
                }

                $html.= '<img src="'.$url.'" '.$widthTag.$alt.$class.' />';

            }
        }

        return new Response($html, Response::HTTP_OK);

    }


}
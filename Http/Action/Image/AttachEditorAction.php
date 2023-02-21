<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Domain\Media\Image\Editor\EditorImageBuilderInterface;
use Aropixel\AdminBundle\Domain\Media\Image\Library\Repository\ImageRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AttachEditorAction
{
    private EditorImageBuilderInterface $editorImageBuilder;
    private ImageRepositoryInterface $imageRepository;


    /**
     * @param EditorImageBuilderInterface $editorImageBuilder
     * @param ImageRepositoryInterface $imageRepository
     */
    public function __construct(EditorImageBuilderInterface $editorImageBuilder, ImageRepositoryInterface $imageRepository)
    {
        $this->editorImageBuilder = $editorImageBuilder;
        $this->imageRepository = $imageRepository;
    }


    /**
     * Attach an Image.
     */
    public function __invoke(Request $request) : Response
    {
        $html = "";

        $images_id = $request->get('images', []);
        $width = $request->get('width', 300);
        $filter = $request->get('filter', null);
        $alt = $request->get('alt', '');

        if ($width=='customfilter') {
            $width = null;
        }

        if ($width=='auto') {
            $width = null;
            $filter = null;
        }

        if (count($images_id)) {

            if (strlen($alt)) $alt = ' alt="'.$alt.'"';

            foreach ($images_id as $image_id) {

                $image = $this->imageRepository->find($image_id);
                $url = $this->editorImageBuilder->buildImageUrl($image, $width, $filter);

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

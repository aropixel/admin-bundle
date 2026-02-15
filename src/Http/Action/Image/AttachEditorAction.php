<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Component\Media\Image\Editor\EditorImageBuilderInterface;
use Aropixel\AdminBundle\Component\Media\Image\Library\Repository\ImageRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AttachEditorAction
{
    public function __construct(
        private readonly EditorImageBuilderInterface $editorImageBuilder,
        private readonly ImageRepositoryInterface $imageRepository
    ) {
    }

    /**
     * Attach an Image.
     */
    public function __invoke(Request $request): Response
    {
        $html = '';
        $json = json_decode($request->getContent());

        $images_id = $json->images ?? [];
        $width = $json->width ?? '300';
        $filter = $json->filter ?? null;
        $alt = $json->alt ?? '';

        if ('customfilter' == $width) {
            $width = null;
        }

        if ('auto' == $width) {
            $width = null;
            $filter = null;
        }

        if (\count($images_id)) {
            if (mb_strlen((string) $alt)) {
                $alt = ' alt="' . $alt . '"';
            }

            foreach ($images_id as $image_id) {
                $image = $this->imageRepository->find($image_id);
                $url = $this->editorImageBuilder->buildImageUrl($image, $width, $filter);

                $class = '';
                $widthTag = ' width="' . $width . '"';
                if ('100pc' == $width) {
                    $class = ' class="img-fluid img-responsive"';
                    $widthTag = '';
                }
                if (null === $width) {
                    $widthTag = '';
                }

                $html .= '<img src="' . $url . '" ' . $widthTag . $alt . $class . ' />';
            }
        }

        return new Response($html, Response::HTTP_OK);
    }
}

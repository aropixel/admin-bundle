<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 17/02/2023 à 18:26
 */

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Editor;

use Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Domain\Media\Image\Editor\EditorImageBuilderInterface;
use Aropixel\AdminBundle\Entity\Image;
use Liip\ImagineBundle\Service\FilterService;

class EditorImageBuilder implements EditorImageBuilderInterface
{

    private PathResolverInterface $pathResolver;
    private FilterService $filterService;

    /**
     * @param PathResolverInterface $pathResolver
     * @param FilterService $filterService
     */
    public function __construct(PathResolverInterface $pathResolver, FilterService $filterService)
    {
        $this->pathResolver = $pathResolver;
        $this->filterService = $filterService;
    }


    public function buildImageUrl(Image $image, ?string $width = null, ?string $filter = null) : string
    {

        // Si aucun filtre mais une largeur
        // on récupère le filtre de largeur préconstruit par le bundle
        if ((is_null($filter) || !strlen($filter)) && $width) {
            $filter = 'editor_'.$width;
        }

        if (!is_null($filter)) {
            $resourcePath = $this->filterService->getUrlOfFilteredImage($image->getWebPath(), $filter);
        }
        else {

            $size = getimagesize($this->pathResolver->getPrivateAbsolutePath($image->getFilename(), Image::UPLOAD_DIR));

            // Runtime configuration
            $runtimeConfig = [
                'relative_resize' => [
                    'widen' => $size[0]
                ],
            ];

            $resourcePath = $this->filterService->getUrlOfFilteredImageWithRuntimeFilters(
                $image->getWebPath(),
                'auto',
                $runtimeConfig
            );

        }

        return $resourcePath;
    }
}

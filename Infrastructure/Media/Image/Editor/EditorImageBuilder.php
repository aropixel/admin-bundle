<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Editor;

use Aropixel\AdminBundle\Domain\Media\Image\Editor\EditorImageBuilderInterface;
use Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\Image;
use Liip\ImagineBundle\Service\FilterService;

class EditorImageBuilder implements EditorImageBuilderInterface
{
    public function __construct(
        private readonly PathResolverInterface $pathResolver,
        private readonly FilterService $filterService
    ) {
    }

    public function buildImageUrl(Image $image, ?string $width = null, ?string $filter = null): string
    {
        // Si aucun filtre mais une largeur
        // on récupère le filtre de largeur préconstruit par le bundle
        if ((null === $filter || !mb_strlen($filter)) && $width) {
            $filter = 'editor_' . $width;
        }

        if (null !== $filter) {
            $resourcePath = $this->filterService->getUrlOfFilteredImage($image->getWebPath(), $filter);
        } else {
            $size = getimagesize($this->pathResolver->getPrivateAbsolutePath($image->getFilename(), Image::UPLOAD_DIR));

            // Runtime configuration
            $runtimeConfig = [
                'relative_resize' => [
                    'widen' => $size[0],
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

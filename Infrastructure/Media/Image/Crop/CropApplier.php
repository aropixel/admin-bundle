<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Crop;

use Aropixel\AdminBundle\Domain\Media\Image\Crop\CropApplierInterface;
use Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\Image;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

class CropApplier implements CropApplierInterface
{
    public function __construct(
        private CacheManager $cacheManager,
        private DataManager $dataManager,
        private FilterManager $filterManager,
        private readonly PathResolverInterface $pathResolver
    ) {
    }

    private function getRatio($imagePath)
    {
        [$realWidth, $realHeight] = getimagesize($imagePath);

        return 600 / $realWidth;
    }

    public function applyCrop(string $fileName, string $filterName, string $cropCoordinates): void
    {
        // Liip imagine services
        $dataManager = $this->dataManager;
        $filterManager = $this->filterManager;
        $cacheManager = $this->cacheManager;

        // Get the filter configuration
        $filterConfiguration = $filterManager->getFilterConfiguration()->get($filterName);

        $imagePath = $this->pathResolver->getPrivateAbsolutePath($fileName, Image::UPLOAD_DIR);
        $ratio = $this->getRatio($imagePath);

        // Merge crop configuration with needed coords into the filter configuration
        $coords = explode(',', $cropCoordinates);
        $cropConfiguration = [
            'crop' => [
                'size' => [$coords[2] / $ratio, $coords[3] / $ratio],
                'start' => [$coords[0] / $ratio, $coords[1] / $ratio],
            ],
        ];

        // Merge the "filters" part of the filter configuration
        $mergedFilters = array_merge($cropConfiguration, $filterConfiguration['filters']);

        // Retrieves the image with the given filter applied
        $relativePath = Image::UPLOAD_DIR . '/' . $fileName;
        $binary = $dataManager->find($filterName, $relativePath);

        // Apply the crop
        $filteredBinary = $filterManager->apply($binary, ['filters' => $mergedFilters]);

        // Store & overwrite image
        $cacheManager->store($filteredBinary, $relativePath, $filterName);
    }
}

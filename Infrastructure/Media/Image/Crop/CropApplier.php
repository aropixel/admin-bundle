<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 11/08/2020 à 16:27
 */

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Crop;


use Aropixel\AdminBundle\Domain\Media\Image\Crop\CropApplierInterface;
use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Resolver\PathResolverInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

class CropApplier implements CropApplierInterface
{

    private CacheManager $cacheManager;
    private DataManager $dataManager;
    private FilterManager $filterManager;
    private PathResolverInterface $pathResolver;


    /**
     * @param CacheManager $cacheManager
     * @param DataManager $dataManager
     * @param FilterManager $filterManager
     * @param PathResolverInterface $pathResolver
     */
    public function __construct(CacheManager $cacheManager, DataManager $dataManager, FilterManager $filterManager, PathResolverInterface $pathResolver)
    {
        $this->cacheManager = $cacheManager;
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->pathResolver = $pathResolver;
    }


    private function getRatio($imagePath) {

        list($realWidth, $realHeight) = getimagesize($imagePath);
        return (600 / $realWidth);
    }


    public function applyCrop(string $fileName, string $filterName, string $cropCoordinates) : void
    {

        // Liip imagine services
        $dataManager = $this->dataManager;
        $filterManager = $this->filterManager;
        $cacheManager = $this->cacheManager;


        // Get the filter configuration
        $filterConfiguration = $filterManager->getFilterConfiguration()->get($filterName);

        //
        $imagePath = $this->pathResolver->getAbsolutePath(Image::UPLOAD_DIR, $fileName);
        $ratio = $this->getRatio($imagePath);

        // Merge crop configuration with needed coords into the filter configuration
        $coords = explode(',', $cropCoordinates);
        $cropConfiguration = [
            'crop' => [
                'size' => array($coords[2] / $ratio, $coords[3] / $ratio),
                'start' => array($coords[0] / $ratio, $coords[1] / $ratio)
            ]
        ];

        // Merge the "filters" part of the filter configuration
        $mergedFilters = array_merge($cropConfiguration, $filterConfiguration['filters']);

        // Retrieves the image with the given filter applied
        $relativeDataRootPath = $this->pathResolver->getDataRootRelativePath(Image::UPLOAD_DIR, $fileName);
        $binary = $dataManager->find($filterName, $relativeDataRootPath);

        // Apply the crop
        $filteredBinary = $filterManager->apply($binary, array(
            'filters' => $mergedFilters
        ));

        // Store & overwrite image
        $cacheManager->store($filteredBinary, $relativeDataRootPath, $filterName);

    }
}

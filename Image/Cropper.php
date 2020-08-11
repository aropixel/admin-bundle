<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 11/08/2020 à 16:27
 */

namespace Aropixel\AdminBundle\Image;


use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

class Cropper
{

    /** @var PathResolver  */
    private $pathResolver;

    /** @var DataManager  */
    private $dataManager;

    /** @var FilterManager  */
    private $filterManager;

    /** @var CacheManager  */
    private $cacheManager;


    /**
     * Cropper constructor.
     * @param PathResolver $pathResolver
     * @param DataManager $dataManager
     * @param FilterManager $filterManager
     * @param CacheManager $cacheManager
     */
    public function __construct(PathResolver $pathResolver, DataManager $dataManager, FilterManager $filterManager, CacheManager $cacheManager)
    {
        $this->pathResolver = $pathResolver;
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;
    }


    private function getRatio($imagePath) {

        list($realWidth, $realHeight) = getimagesize($imagePath);
        $ratio = 600 / $realWidth;

        return $ratio;
    }


    public function applyCrop($fileName, $filterName, $cropCoordinates)
    {

        // Liip imagine services
        $dataManager = $this->dataManager;
        $filterManager = $this->filterManager;
        $cacheManager = $this->cacheManager;


        // Get the filter configuration
        $filterConfiguration = $filterManager->getFilterConfiguration()->get($filterName);

        //
        $imagePath = $this->pathResolver->getAbsolutePath($fileName);
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
        $relativeDataRootPath = $this->pathResolver->getDataRootRelativePath($fileName);
        $binary = $dataManager->find($filterName, $relativeDataRootPath);

        // Apply the crop
        $filteredBinary = $filterManager->apply($binary, array(
            'filters' => $mergedFilters
        ));

        // Store & overwrite image
        $cacheManager->store($filteredBinary, $relativeDataRootPath, $filterName);

    }
}

<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 13/02/2023 à 10:51
 */

namespace Aropixel\AdminBundle\Infrastructure\Media\Crop\Twig;

use Aropixel\AdminBundle\Domain\Media\Image\Crop\AvailableCropProviderInterface;
use Aropixel\AdminBundle\Entity\CroppableInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CropExtension extends AbstractExtension
{
    private AvailableCropProviderInterface $availableCropProvider;


    /**
     * @param AvailableCropProviderInterface $availableCropProvider
     */
    public function __construct(AvailableCropProviderInterface $availableCropProvider)
    {
        $this->availableCropProvider = $availableCropProvider;
    }


    public function getFunctions() : array
    {
        return array(
            new TwigFunction('get_available_crop_filters', array($this, 'getAvailableCropFilters')),
            new TwigFunction('get_class_available_crop_filters', array($this, 'getClassAvailableCropFilters')),
        );
    }


    public function getAvailableCropFilters(?CroppableInterface $croppable, ?array $availableCropList=null) : array
    {
        return $this->availableCropProvider->getAvailableCropFilters($croppable, $availableCropList);
    }


    public function getClassAvailableCropFilters(string $className) : array
    {
        return $this->availableCropProvider->getAvailableCropFilters(new $className());
    }

}

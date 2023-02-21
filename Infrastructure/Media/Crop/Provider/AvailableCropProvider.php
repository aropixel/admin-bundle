<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 13/02/2023 à 13:19
 */

namespace Aropixel\AdminBundle\Infrastructure\Media\Crop\Provider;

use Aropixel\AdminBundle\Domain\Media\Image\Crop\AvailableCropFilter;
use Aropixel\AdminBundle\Domain\Media\Image\Crop\AvailableCropProviderInterface;
use Aropixel\AdminBundle\Entity\CroppableInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AvailableCropProvider implements AvailableCropProviderInterface
{
    private ParameterBagInterface $parameterBag;

    /**
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function getAvailableCropFilters(?CroppableInterface $croppable, ?array $configuredFilters = null): array
    {
        $availableCropList = [];
dump($croppable);
        // Get crops already applied and saved
        $imageRegisteredCrops = $croppable ? $croppable->getCropsInfos() : [];

        // Get all liip filters
        $liipFilters = $this->parameterBag->get('liip_imagine.filter_sets');

        // Get configured filters for given image type
        if (is_null($configuredFilters)) {
            $configuredFilters = !is_null($croppable) ? $this->findConfiguredFilters($croppable) : [];
        }

        foreach ($configuredFilters as $slug => $description) {

            if (!array_key_exists($slug, $liipFilters)) {
                continue;
            }

            $liipFilterConfiguration = $liipFilters[$slug];

            // Si ce filtre ne contient pas de miniature (juste un resize par exemple)
            // on ne le prend pas en compte
            if (!isset($liipFilterConfiguration['filters']['thumbnail']))        continue;


            // Calcule le ratio du filtre
            $ratio = $liipFilterConfiguration['filters']['thumbnail']['size'][0] / $liipFilterConfiguration['filters']['thumbnail']['size'][1];


            // Construit les infos de retour
            $filter = new AvailableCropFilter();
            $filter->coordinates = array_key_exists($slug, $imageRegisteredCrops) ? $imageRegisteredCrops[$slug] : "";
            $filter->ratio = $ratio;
            $filter->slug = $slug;
            $filter->description = $description;

            $availableCropList[$slug] = $filter;

        }

        return $availableCropList;
    }

    private function findConfiguredFilters(CroppableInterface $croppable) : array
    {
        $imageClass = get_class($croppable);
        $imageClass = str_replace('Proxies\__CG__\\', '', $imageClass);

        $configuredFilters = $this->parameterBag->get('aropixel_admin.filter_sets');
        return array_key_exists($imageClass, $configuredFilters) ? $configuredFilters[$imageClass] : [];
    }
}

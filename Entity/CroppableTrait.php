<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 14/02/2023 à 13:25
 */

namespace Aropixel\AdminBundle\Entity;

use Doctrine\Common\Collections\Collection;

trait CroppableTrait
{
    /**
     * @return Collection<Crop>
     */
    public function getCrops(): Collection
    {
        return $this->crops;
    }


    public function getImageUid(): string
    {
        return $this->getId() ?: uniqid();
    }

    public function getCropsInfos() : array
    {
        $cropsInfos = [];

        foreach ($this->getCrops() as $crop) {
            $cropsInfos[$crop->getFilter()] = $crop;
        }

        return $cropsInfos;
    }
}

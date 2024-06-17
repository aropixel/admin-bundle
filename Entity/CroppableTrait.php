<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 14/02/2023 à 13:25
 */

namespace Aropixel\AdminBundle\Entity;

use Aropixel\AdminBundle\Entity\Crop;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait CroppableTrait
{
    public function getCrops(): Collection
    {
        if ($this->crops === null) {
            $this->crops = new ArrayCollection((array) $this->crops);
        }

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
            $cropsInfos[$crop->getFilter()] = $crop->getCrop();
        }

        return $cropsInfos;
    }
}

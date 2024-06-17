<?php

namespace Aropixel\AdminBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait CroppableTrait
{
    public function getCrops(): Collection
    {
        if (null === $this->crops) {
            $this->crops = new ArrayCollection((array) $this->crops);
        }

        return $this->crops;
    }

    public function getImageUid(): string
    {
        return $this->getId() ?: uniqid();
    }

    public function getCropsInfos(): array
    {
        $cropsInfos = [];

        foreach ($this->getCrops() as $crop) {
            $cropsInfos[$crop->getFilter()] = $crop->getCrop();
        }

        return $cropsInfos;
    }
}

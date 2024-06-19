<?php

namespace Aropixel\AdminBundle\Entity;

use Aropixel\AdminBundle\Entity\CropInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

abstract class Crop implements CropInterface
{
    private string $filter;

    private ?string $crop = null;

    private ?\DateTime $createdAt = null;

    private ?\DateTime $updatedAt = null;

    public function setFilter(string $filter): CropInterface
    {
        $this->filter = $filter;

        return $this;
    }

    public function getFilter(): string
    {
        return $this->filter;
    }

    public function setCrop(?string $crop): CropInterface
    {
        $this->crop = $crop;

        return $this;
    }

    public function getCrop(): ?string
    {
        return $this->crop;
    }

    public function setCreatedAt(?\DateTime $createdAt): CropInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): CropInterface
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}

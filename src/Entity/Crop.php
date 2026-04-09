<?php

namespace Aropixel\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\MappedSuperclass]
abstract class Crop implements CropInterface
{
    #[ORM\Column(type: 'string')]
    private string $filter;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $crop = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTime $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
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

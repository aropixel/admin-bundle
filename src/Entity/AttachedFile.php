<?php

namespace Aropixel\AdminBundle\Entity;

use Aropixel\AdminBundle\Entity\File;
use Aropixel\AdminBundle\Entity\FileInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

abstract class AttachedFile
{
    private ?string $title = null;

    private ?string $alt = null;

    private int $position = 0;

    private ?FileInterface $file = null;

    private ?\DateTime $createdAt = null;

    private ?\DateTime $updatedAt = null;

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setAlt(?string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setFile(?File $file = null): self
    {
        $this->file = $file;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * Get image url.
     */
    public function getWebPath(): ?string
    {
        return $this->file?->getWebPath();
    }

    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}

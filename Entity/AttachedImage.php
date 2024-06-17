<?php

namespace Aropixel\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\MappedSuperclass]
class AttachedImage implements AttachedImageInterface
{
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $link = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $attrTitle = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $attrAlt = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $attrClass = null;

    #[ORM\Column(type: 'integer')]
    #[Gedmo\SortablePosition]
    private int $position = 0;

    #[ORM\ManyToOne(targetEntity: ImageInterface::class)]
    private ?ImageInterface $image = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTime $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt = null;

    private ?ImageInterface $oldImage = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAttrTitle(): ?string
    {
        return $this->attrTitle;
    }

    public function setAttrTitle(?string $attrTitle): self
    {
        $this->attrTitle = $attrTitle;

        return $this;
    }

    public function getAttrAlt(): ?string
    {
        return $this->attrAlt;
    }

    public function setAttrAlt(?string $attrAlt): self
    {
        $this->attrAlt = $attrAlt;

        return $this;
    }

    public function getAttrClass(): ?string
    {
        return $this->attrClass;
    }

    public function setAttrClass(?string $attrClass): self
    {
        $this->attrClass = $attrClass;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getImage(): ?ImageInterface
    {
        return $this->image;
    }

    public function setImage(?ImageInterface $image): self
    {
        if (null !== $this->image) {
            $this->oldImage = clone $this->image;
        } else {
            $this->oldImage = null;
        }

        $this->image = $image;

        return $this;
    }

    public function getOldImage(): ?ImageInterface
    {
        return $this->oldImage;
    }

    public function setOldImage(?ImageInterface $oldImage): self
    {
        $this->oldImage = $oldImage;

        return $this;
    }

    public function hasImageChanged(): bool
    {
        return $this->oldImage !== $this->image;
    }

    public function getFilename(): ?string
    {
        return $this->image?->getFilename();
    }

    public function getWebPath()
    {
        return $this->image?->getWebPath();
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}

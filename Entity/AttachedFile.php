<?php
/**
 * Créé par Aropixel @2017.
 * Par: Joël Gomez Caballe
 * Date: 10/02/2017 à 16:27
 */

namespace Aropixel\AdminBundle\Entity;


use Aropixel\AdminBundle\Entity\File;
use Aropixel\AdminBundle\Entity\FileInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\MappedSuperclass]
abstract class AttachedFile
{
    #[ORM\Column(type: "string", nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $alt = null;

    #[ORM\Column(type: "integer")]
    #[Gedmo\SortablePosition]
    private int $position = 0;

    #[ORM\ManyToOne(targetEntity: FileInterface::class)]
    private ?FileInterface $file = null;

    #[Gedmo\Timestampable(on: "create")]
    #[ORM\Column(name: "created_at", type: "datetime")]
    private ?\DateTime $createdAt = null;

    #[Gedmo\Timestampable(on: "update")]
    #[ORM\Column(name: "updated_at", type: "datetime", nullable: true)]
    private ?\DateTime $updatedAt = null;


    public function setTitle(?string $title) : AttachedFile
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle() : ?string
    {
        return $this->title;
    }

    public function setAlt(?string $alt) : AttachedFile
    {
        $this->alt = $alt;

        return $this;
    }

    public function getAlt() : ?string
    {
        return $this->alt;
    }

    public function setPosition(int $position) : AttachedFile
    {
        $this->position = $position;

        return $this;
    }

    public function getPosition() : int
    {
        return $this->position;
    }

    public function setFile(File $file = null) : AttachedFile
    {
        $this->file = $file;

        return $this;
    }

    public function getFile() : ?File
    {
        return $this->file;
    }

    /**
     * Get image url
     *
     * @return string
     */
    public function getWebPath() : ?string
    {
        return $this->file?->getWebPath();
    }


    public function setCreatedAt(?\DateTime $createdAt) : AttachedFile
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt() : ?\DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt) : AttachedFile
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt() : ?\DateTime
    {
        return $this->updatedAt;
    }

}

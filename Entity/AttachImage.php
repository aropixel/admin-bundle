<?php
/**
 * Créé par Aropixel @2017.
 * Par: Joël Gomez Caballe
 * Date: 10/02/2017 à 16:27
 */

namespace Aropixel\AdminBundle\Entity;

use Aropixel\AdminBundle\Entity\ImageInterface;
use Aropixel\AdminBundle\Entity\ItemLibraryInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;


#[ORM\MappedSuperclass]
abstract class AttachImage implements ImageInterface
{

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $link = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $attrTitle = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $attrAlt = null;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $attrClass = null;

    #[ORM\Column(type: "integer")]
    #[Gedmo\SortablePosition]
    private int $position = 0;

    #[ORM\ManyToOne(targetEntity: ImageInterface::class)]
    private ?ImageInterface $image = null;

    #[Gedmo\Timestampable(on: "create")]
    #[ORM\Column(name: "created_at", type: "datetime")]
    private ?\DateTime $createdAt = null;

    #[Gedmo\Timestampable(on: "update")]
    #[ORM\Column(name: "updated_at", type: "datetime", nullable: true)]
    private ?\DateTime $updatedAt = null;

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return AttachImage
     */
    public function setTitle(?string $title): AttachImage
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @param string|null $link
     * @return AttachImage
     */
    public function setLink(?string $link): AttachImage
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return AttachImage
     */
    public function setDescription(?string $description): AttachImage
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttrTitle(): ?string
    {
        return $this->attrTitle;
    }

    /**
     * @param string|null $attrTitle
     * @return AttachImage
     */
    public function setAttrTitle(?string $attrTitle): AttachImage
    {
        $this->attrTitle = $attrTitle;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttrAlt(): ?string
    {
        return $this->attrAlt;
    }

    /**
     * @param string|null $attrAlt
     * @return AttachImage
     */
    public function setAttrAlt(?string $attrAlt): AttachImage
    {
        $this->attrAlt = $attrAlt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttrClass(): ?string
    {
        return $this->attrClass;
    }

    /**
     * @param string|null $attrClass
     * @return AttachImage
     */
    public function setAttrClass(?string $attrClass): AttachImage
    {
        $this->attrClass = $attrClass;
        return $this;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     * @return AttachImage
     */
    public function setPosition(int $position): AttachImage
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return ItemLibraryInterface
     */
    public function getImage(): ?ItemLibraryInterface
    {
        return $this->image;
    }

    /**
     * @param ItemLibraryInterface $image
     * @return AttachImage
     */
    public function setImage(?ItemLibraryInterface $image): AttachImage
    {
        if (!is_null($this->image)) {
            $this->oldImage = clone ($this->image);
        }
        else {
            $this->oldImage = null;
        }

        $this->image = $image;
        return $this;
    }

    /**
     * @return ItemLibraryInterface|null
     */
    public function getOldImage(): ?ItemLibraryInterface
    {
        return $this->oldImage;
    }

    /**
     * @param ItemLibraryInterface|null $oldImage
     * @return AttachImage
     */
    public function setOldImage(?ItemLibraryInterface $oldImage): AttachImage
    {
        $this->oldImage = $oldImage;
        return $this;
    }

    /**
     * Check if image has changed
     *
     * @return boolean
     */
    public function hasImageChanged()
    {
        return ($this->oldImage != $this->image);
    }


    public function getFilename() : ?string
    {
        return $this->image ? $this->image->getFilename() : null;
    }

    public function getWebPath()
    {
        return $this->image ? $this->image->getWebPath() : null;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     * @return AttachImage
     */
    public function setCreatedAt(?\DateTime $createdAt): AttachImage
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|null $updatedAt
     * @return AttachImage
     */
    public function setUpdatedAt(?\DateTime $updatedAt): AttachImage
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }


}

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


/**
 * Abstract class to handle image attachment
 */
abstract class AttachImage implements ImageInterface
{

    protected ?string $title;

    protected ?string $link;

    protected ?string $description;

    protected ?string $attrTitle;

    protected ?string $attrAlt;

    /**
     * Specific class to give when rendering the image
     */
    protected ?string $attrClass;

    /**
     * Position when the image is part of a set of images
     */
    protected int $position = 0;

    /**
     * Image library selected
     */
    protected ?ItemLibraryInterface $image = null;

    /**
     * Used when image is changed
     */
    protected ?ItemLibraryInterface $oldImage = null;


    protected ?\DateTime $createdAt = null;

    protected ?\DateTime $updatedAt = null;

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

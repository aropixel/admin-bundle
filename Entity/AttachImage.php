<?php
/**
 * Créé par Aropixel @2017.
 * Par: Joël Gomez Caballe
 * Date: 10/02/2017 à 16:27
 */

namespace Aropixel\AdminBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;


/**
 * Abstract class to handle image attachment
 */
abstract class AttachImage implements ImageInterface
{

    /**
     * @var string Title of the image
     */
    protected $title;

    /**
     * @var string Link of the image
     */
    protected $link;

    /**
     * @var string Description of the image
     */
    protected $description;

    /**
     * @var string Html "title" attribute of the image
     */
    protected $attrTitle;

    /**
     * @deprecated Use attrAlt instead. Will be removed in v2.
     */
    protected $alt;

    /**
     * @var string Html "alt" attribute of the image
     */
    protected $attrAlt;

    /**
     * @var string Specific class to give when rendering the image
     */
    protected $attrClass;

    /**
     * @deprecated Use attrAlt instead. Will be removed in v2.
     */
    protected $class;

    /**
     * @var integer Position when the image is part of a set of images
     */
    protected $position = 0;

    /**
     * @var ImageInterface  Image entity that represent image source
     */
    protected $image;

    /**
     * @var ImageInterface
     */
    protected $oldImage;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     * @return AttachImage
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string|null $link
     * @return AttachImage
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return AttachImage
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttrTitle()
    {
        return $this->attrTitle;
    }

    /**
     * @param string|null $attrTitle
     * @return AttachImage
     */
    public function setAttrTitle($attrTitle)
    {
        $this->attrTitle = $attrTitle;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttrAlt()
    {
        return $this->attrAlt;
    }

    /**
     * @param string|null $attrAlt
     * @return AttachImage
     */
    public function setAttrAlt($attrAlt)
    {
        $this->attrAlt = $attrAlt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * @param mixed $alt
     * @return AttachImage
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttrClass()
    {
        return $this->attrClass;
    }

    /**
     * @param string|null $attrClass
     * @return AttachImage
     */
    public function setAttrClass($attrClass)
    {
        $this->attrClass = $attrClass;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     * @return AttachImage
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return AttachImage
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set image
     *
     * @param Image $image
     *
     * @return $this
     */
    public function setImage(ImageInterface $image = null)
    {
        //
        if (!is_null($this->image)) {
            $this->oldImage = clone ($this->image);
        }
        else {
            $this->oldImage = null;
        }

        //
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return ImageInterface
     */
    public function getImage()
    {
        return $this->image;
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

    /**
     * Get image url
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->image ? $this->image->getFilename() : null;
    }

    /**
     * Get image url
     *
     * @return string
     */
    public function getWebPath()
    {
        return $this->image ? $this->image->getWebPath() : null;
    }

    /**
     * Get image url
     *
     * @return string
     */
    public function getAbsolutePath()
    {
        return $this->image ? $this->image->getAbsolutePath() : null;
    }

    /**
     * Get image url
     *
     * @return string
     */
    public function fileExists()
    {
        $absolutePath = $this->image ? $this->image->getAbsolutePath() : null;
        if ($absolutePath && file_exists($absolutePath)) {
            return true;
        }
    }


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return AttachImage
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return AttachImage
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

}

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
abstract class AttachImage
{

    /**
     * @var string  Title html attribute of the image
     */
    protected $title;

    /**
     * @var string  Attr html attribute of the image
     */
    protected $alt;

    /**
     * @var string  Specific class to give when rendering the image
     */
    protected $class;

    /**
     * @var integer Position when the image is part of a set of images
     */
    protected $position;

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
     * Set title
     *
     * @param string $title
     *
     * @return AttachImage
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set alt
     *
     * @param string $alt
     *
     * @return AttachImage
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * Set class
     *
     * @param string $class
     *
     * @return AttachImage
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * Get class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
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

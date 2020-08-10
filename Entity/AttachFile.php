<?php
/**
 * Créé par Aropixel @2017.
 * Par: Joël Gomez Caballe
 * Date: 10/02/2017 à 16:27
 */

namespace Aropixel\AdminBundle\Entity;



/**
 * Abstract class to handle file attachment
 */
abstract class AttachFile
{

    /**
     * @var string  Html title attribute for the file link rendering
     */
    protected $title;

    /**
     * @var string  Html alt attribute for the file link rendering
     */
    protected $alt;

    /**
     * @var integer Position when the file is part of a set of files
     */
    protected $position;

    /**
     * @var FileInterface  File entity that represent file source
     */
    protected $file;

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
     * @return AttachFile
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
     * @return AttachFile
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
     * Set position
     *
     * @param integer $position
     *
     * @return AttachFile
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
     * @param Fichier $file
     *
     * @return AttachFile
     */
    public function setFile(Fichier $file = null)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get image
     *
     * @return Fichier
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Check wether image exists
     *
     * @return bool
     */
    public function fileExists()
    {
        $absolutePath = $this->file ? $this->file->getAbsolutePath() : null;
        if ($absolutePath && file_exists($absolutePath)) {
            return true;
        }

        return false;
    }

    /**
     * Get image url
     *
     * @return string
     */
    public function getWebPath()
    {
        return $this->file ? $this->file->getWebPath() : null;
    }

    /**
     * Get image url
     *
     * @return string
     */
    public function getAbsolutePath()
    {
        return $this->file ? $this->file->getAbsolutePath() : null;
    }


    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return AttachFile
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
     * @return AttachFile
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

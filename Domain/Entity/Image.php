<?php

namespace Aropixel\AdminBundle\Domain\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Image
 */
class Image implements ImageInterface
{

    const UPLOAD_DIR = 'images';

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string  Image title
     */
    protected $titre;

    /**
     * @var string  Regroup images for displaying specific library images
     */
    protected $category;

    /**
     * @var string  Temporary path (not mapped)
     */
    protected $temp;

    /**
     * @var UploadedFile    File object of the image
     * @Assert\File()
     */
    public $file;

    /**
     * @var string  Default title attribute (can be overrided by AttachImage)
     */
    protected $attrTitle;

    /**
     * @var string  Default alt attribute (can be overrided by AttachImage)
     */
    protected $attrAlt;

    /**
     * @var string  Description of the image
     */
    protected $attrDescription;

    /**
     * @var string  Image filename
     */
    protected $filename;

    /**
     * @var string  Image type: filename extension
     */
    protected $extension;

    /**
     * @var string  URL used if the image was imported (not uploaded)
     */
    protected $import;

    /**
     * @var boolean
     */
    protected $isNew;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->crops = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set titre
     *
     * @param string $titre
     * @return Image
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set attrTitle
     *
     * @param string $attrTitle
     * @return Image
     */
    public function setAttrTitle($attrTitle)
    {
        $this->attrTitle = $attrTitle;

        return $this;
    }

    /**
     * Get attrTitle
     *
     * @return string
     */
    public function getAttrTitle()
    {
        return $this->attrTitle;
    }

    /**
     * Set attrAlt
     *
     * @param string $attrAlt
     * @return Image
     */
    public function setAttrAlt($attrAlt)
    {
        $this->attrAlt = $attrAlt;

        return $this;
    }

    /**
     * Get attrAlt
     *
     * @return string
     */
    public function getAttrAlt()
    {
        return $this->attrAlt;
    }

    /**
     * Set attrDescription
     *
     * @param string $attrDescription
     * @return Image
     */
    public function setAttrDescription($attrDescription)
    {
        $this->attrDescription = $attrDescription;

        return $this;
    }

    /**
     * Get attrDescription
     *
     * @return string
     */
    public function getAttrDescription()
    {
        return $this->attrDescription;
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return Image
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set extension
     *
     * @param string $extension
     * @return Image
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Image
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
     * @return Image
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

    /**
     * Get image url
     *
     * @return string
     */
    public function getWebPath()
    {
        return null === $this->filename ? null : $this->getUploadDir().'/'.$this->filename;
    }


    static function getFileNameWebPath($fileName)
    {
        return null === $fileName ? null : self::UPLOAD_DIR.'/'.$fileName;
    }

    /**
     * Get image absolute path
     *
     * @return string
     */
    protected function getUploadDir()
    {
        // on se débarrasse de « __DIR__ » afin de ne pas avoir de problème lorsqu'on affiche
        // le document/image dans la vue.
        return self::UPLOAD_DIR;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }


    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        // check if we have an old image path
        if (isset($this->path)) {
            // store the old name to delete after the update
            $this->temp = $this->path;
            $this->path = null;
        } else {
            $this->path = 'initial';
        }
    }


    public function getTempPath()
    {
        return $this->temp;
    }


    /**
     * Set category
     *
     * @param string $category
     * @return Image
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add crops
     *
     * @param Crop $crops
     * @return Image
     */
    public function addCrop(Crop $crops)
    {
        $this->crops[] = $crops;

        return $this;
    }

    /**
     * Remove crops
     *
     * @param Crop $crops
     */
    public function removeCrop(Crop $crops)
    {
        $this->crops->removeElement($crops);
    }

    /**
     * Get crops
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCrops()
    {
        return $this->crops;
    }

    /**
     * Set import
     *
     * @param string $import
     * @return Image
     */
    public function setImport($import)
    {
        $this->import = $import;

        return $this;
    }

    /**
     * Get import
     *
     * @return string
     */
    public function getImport()
    {
        return $this->import;
    }

    /**
     * Set isNew
     *
     * @param string $import
     * @return Image
     */
    public function setIsNew($is_new)
    {
        $this->isNew = $is_new;

        return $this;
    }

    /**
     * Get import
     *
     * @return string
     */
    public function isNew()
    {
        return $this->isNew;
    }
}

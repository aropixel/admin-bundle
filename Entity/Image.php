<?php

namespace Aropixel\AdminBundle\Entity;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Image
 */
class Image implements ItemLibraryInterface
{

    const UPLOAD_DIR = 'images';

    protected ?int $id;

    /**
     * Image title
     */
    protected string $title;

    /**
     * Used in order to display image in his specific library
     */
    protected string $category;

    /**
     * Temporary path (not mapped)
     */
    protected ?string $temp = null;

    /**
     * File object of the image
     * @Assert\File()
     */
    public ?File $file;

    /**
     * Default title attribute (can be overrided in AttachImage)
     */
    protected ?string $attrTitle;

    /**
     * Default alt attribute (can be overrided by AttachImage)
     */
    protected ?string $attrAlt;

    /**
     * Description of the image
     */
    protected ?string $description;

    /**
     * Image filename
     */
    protected ?string $filename;

    /**
     * Filename extension
     */
    protected ?string $extension;

    /**
     * URL used of the imported image (if not uploaded)
     */
    protected ?string $import;

    /**
     * Is the image just created (not mapped)
     */
    protected bool $isNew = false;


    protected ?\DateTime $createdAt;
    protected ?\DateTime $updatedAt;



    /**
     * Get id
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * The image's title in the image library
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @deprecated
     */
    public function getTitre() : string
    {
        return $this->getTitle();
    }

    /**
     * The image's title in the image library
     */
    public function setTitle(string $title): Image
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @deprecated
     */
    public function setTitre(string $title) : Image
    {
        return $this->setTitle($title);
    }

    /**
     * Default title attribute
     */
    public function setDefaultAttrTitle(?string $attrTitle) : Image
    {
        $this->attrTitle = $attrTitle;
        return $this;
    }

    /**
     * Default title attribute
     */
    public function getDefaultAttrTitle() : ?string
    {
        return $this->attrTitle;
    }

    /**
     * Default alt attribute
     */
    public function setDefaultAttrAlt(?string $attrAlt) : Image
    {
        $this->attrAlt = $attrAlt;

        return $this;
    }

    /**
     * Default alt attribute
     */
    public function getDefaultAttrAlt() : ?string
    {
        return $this->attrAlt;
    }

    /**
     * Set image's description
     */
    public function setDescription($description) : Image
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set image's description
     */
    public function getDescription() : ?string
    {
        return $this->description;
    }

    /**
     * Set filename
     */
    public function setFilename($filename) : Image
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Get filename
     */
    public function getFilename() : ?string
    {
        return $this->filename;
    }

    /**
     * Set extension
     */
    public function setExtension($extension) : Image
    {
        $this->extension = $extension;
        return $this;
    }

    /**
     * Get extension
     */
    public function getExtension() : ?string
    {
        return $this->extension;
    }

    /**
     * Set createdAt
     */
    public function setCreatedAt($createdAt) : Image
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     */
    public function getCreatedAt() : ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     */
    public function setUpdatedAt($updatedAt) : Image
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get updatedAt
     */
    public function getUpdatedAt() : ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Get image url
     */
    public function getWebPath() : ?string
    {
        return null === $this->filename ? null : $this->getUploadDir().'/'.$this->filename;
    }


    static function getFileNameWebPath(?string $fileName) : ?string
    {
        return null === $fileName ? null : self::UPLOAD_DIR.'/'.$fileName;
    }

    /**
     * Get image directory
     */
    protected function getUploadDir()
    {
        return self::UPLOAD_DIR;
    }

    /**
     * Get file.
     */
    public function getFile() : File
    {
        return $this->file;
    }


    /**
     * Sets file.
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


    public function getTempPath() : ?string
    {
        return $this->temp;
    }


    /**
     * Set category
     */
    public function setCategory($category) : Image
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Get category
     */
    public function getCategory() : string
    {
        return $this->category;
    }

    /**
     * Set import
     */
    public function setImport(?string $import) : Image
    {
        $this->import = $import;
        return $this;
    }

    /**
     * Get import
     */
    public function getImport() : ?string
    {
        return $this->import;
    }

    /**
     * Set isNew
     */
    public function setIsNew(bool $isNew) : Image
    {
        $this->isNew = $isNew;
        return $this;
    }

    /**
     * Get import
     *
     * @return string
     */
    public function isNew() : bool
    {
        return $this->isNew;
    }
}

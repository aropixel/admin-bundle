<?php

namespace Aropixel\AdminBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

/**
 * File
 */
class File implements FileInterface
{

    const UPLOAD_DIR = 'files';

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string  File title
     */
    protected $title;

    /**
     * @var string  Regroup files for displaying specific files libraries
     */
    protected $category;

    /**
     * @var string  Temporary path (not mapped)
     */
    protected $temp;

    /**
     * @var boolean  Is the file public ?
     */
    protected $public;

    /**
     * @Assert\File()
     */
    public $file;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @var string
     */
    protected $import;

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


    public function setTitle(string $title) : self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @deprecated
     */
    public function setTitre(string $title) : self
    {
        return $this->setTitle($title);
    }

    /**
     * @deprecated
     */
    public function getTitre() : string
    {
        return $this->getTitle();
    }

    /**
     * Set attrDescription
     *
     * @param string $attrDescription
     * @return self
     */
    public function setDescription($attrDescription)
    {
        $this->description = $attrDescription;

        return $this;
    }

    /**
     * Get attrDescription
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return self
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
    public function getFilename() : ?string
    {
        return $this->filename;
    }

    /**
     * Set extension
     *
     * @param string $extension
     * @return self
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
     * @return bool
     */
    public function isPublic(): ?bool
    {
        return $this->public;
    }

    /**
     * @param bool $public
     * @return File
     */
    public function setPublic(?bool $public): File
    {
        $this->public = $public;
        return $this;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return self
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
     * @return self
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
     */
    public function getFile() : ?SymfonyFile
    {
        return $this->file;
    }


    /**
     * Sets file.
     */
    public function setFile(SymfonyFile $file = null)
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
     *
     * @param string $category
     * @return self
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
     * Set import
     *
     * @param string $import
     * @return self
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


    public function getRewrittenFileName(): string
    {
        return $this->title . '.' . $this->getExtension();
    }

}

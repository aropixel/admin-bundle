<?php

namespace Aropixel\AdminBundle\Entity;

use Aropixel\AdminBundle\Infrastructure\Media\File\Library\Repository\FileRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

#[ORM\MappedSuperclass]
#[ORM\Table(name: "aropixel_file")]
#[ORM\Entity(repositoryClass: FileRepository::class)]
class File implements FileInterface
{

    const UPLOAD_DIR = 'files';

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    private int $id;

    #[ORM\Column(type: "string")]
    private string $title;

    #[ORM\Column(type: "string")]
    private string $category;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "string")]
    private string $filename;

    #[ORM\Column(type: "string", length: 20)]
    private string $extension;

    #[ORM\Column(type: "boolean")]
    private bool $public;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $import = null;

    #[Gedmo\Timestampable(on: "create")]
    #[ORM\Column(name: "created_at", type: "datetime")]
    private ?\DateTime $createdAt = null;

    #[Gedmo\Timestampable(on: "update")]
    #[ORM\Column(name: "updated_at", type: "datetime", nullable: true)]
    private ?\DateTime $updatedAt = null;

    #[Assert\File]
    public ?SymfonyFile $file = null;

    private ?string $temp = null;

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


    /**
     * Get rewritedFileName
     *
     * @return string
     */
    public function getRewritedFileName():string
    {
        return $this->title.".".$this->getExtension();
    }

}

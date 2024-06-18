<?php

namespace Aropixel\AdminBundle\Entity;

use Aropixel\AdminBundle\Infrastructure\Media\Image\Library\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\MappedSuperclass(repositoryClass: ImageRepository::class)]
#[ORM\Table(name: 'aropixel_image')]
class Image implements ItemLibraryInterface
{
    public const UPLOAD_DIR = 'images';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string')]
    protected ?string $title = null;

    #[ORM\Column(type: 'string')]
    protected ?string $category = null;

    protected ?string $temp = null;

    /**
     * @Assert\File()
     */
    public ?File $file = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $attrTitle = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $attrAlt = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string')]
    private ?string $filename = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $extension = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $import = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false)]
    private ?\DateTime $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getTemp(): ?string
    {
        return $this->temp;
    }

    public function setTemp(?string $temp): void
    {
        $this->temp = $temp;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    public function getAttrTitle(): ?string
    {
        return $this->attrTitle;
    }

    public function setAttrTitle(?string $attrTitle): self
    {
        $this->attrTitle = $attrTitle;

        return $this;
    }

    public function getAttrAlt(): ?string
    {
        return $this->attrAlt;
    }

    public function setAttrAlt(?string $attrAlt): self
    {
        $this->attrAlt = $attrAlt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(?string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getImport(): ?string
    {
        return $this->import;
    }

    public function getTempPath(): ?string
    {
        return $this->temp;
    }

    public function setImport(?string $import): self
    {
        $this->import = $import;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }

    public function setIsNew(bool $isNew): self
    {
        $this->isNew = $isNew;

        return $this;
    }

    public function getWebPath(): ?string
    {
        return null === $this->filename ? null : $this->getUploadDir() . '/' . $this->filename;
    }

    public static function getFileNameWebPath(?string $fileName): ?string
    {
        return null === $fileName ? null : self::UPLOAD_DIR . '/' . $fileName;
    }

    protected function getUploadDir(): string
    {
        return self::UPLOAD_DIR;
    }
}

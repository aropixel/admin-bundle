<?php

namespace Aropixel\AdminBundle\Entity;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class Image implements ImageInterface
{
    public const UPLOAD_DIR = 'images';

    protected ?int $id = null;

    protected ?string $title = null;

    protected ?string $category = null;

    protected ?string $temp = null;

    #[Assert\File]
    public ?File $file = null;

    private ?string $attrTitle = null;

    private ?string $attrAlt = null;

    private ?string $description = null;

    private ?string $filename = null;

    private ?int $width = null;

    private ?int $height = null;

    private ?string $extension = null;

    private ?string $import = null;

    private ?\DateTime $createdAt = null;

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

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
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

    public function setFile(?File $file): void
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

    public static function getFileNameWebPath(?string $fileName): ?string
    {
        return null === $fileName ? null : self::UPLOAD_DIR . '/' . $fileName;
    }

    protected function getUploadDir(): string
    {
        return self::UPLOAD_DIR;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): void
    {
        $this->width = $width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): void
    {
        $this->height = $height;
    }
}

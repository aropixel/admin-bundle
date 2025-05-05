<?php

namespace Aropixel\AdminBundle\Entity;

use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\Validator\Constraints as Assert;

class File implements FileInterface
{
    public const UPLOAD_DIR = 'files';

    private ?int $id = null;

    private string $title;

    private ?string $category = null;

    private ?string $description = null;

    private ?string $filename = null;

    private string $extension;

    private bool $public;

    private ?string $import = null;

    private ?\DateTime $createdAt = null;

    private ?\DateTime $updatedAt = null;

    #[Assert\File]
    public ?SymfonyFile $file = null;

    private ?string $temp = null;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(?string $attrDescription): self
    {
        $this->description = $attrDescription;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(?bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    public function setCreatedAt(?\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function getFile(): ?SymfonyFile
    {
        return $this->file;
    }

    public function setFile(?SymfonyFile $file = null): void
    {
        $this->file = $file;
    }

    public function getTempPath(): ?string
    {
        return $this->temp;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setImport(?string $import): self
    {
        $this->import = $import;

        return $this;
    }

    public function getImport(): ?string
    {
        return $this->import;
    }

    public function getRewrittenFileName(): string
    {
        return $this->title . '.' . $this->getExtension();
    }
}

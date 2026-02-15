<?php

namespace Aropixel\AdminBundle\Entity;

use Aropixel\AdminBundle\Repository\FileRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\MappedSuperclass(repositoryClass: FileRepository::class)]
#[ORM\Table(name: 'aropixel_file')]
class File implements FileInterface
{
    public const UPLOAD_DIR = 'files';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    private string $title;

    #[ORM\Column(type: 'string')]
    private ?string $category = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string')]
    private ?string $filename = null;

    #[ORM\Column(type: 'string', length: 20)]
    private string $extension;

    #[ORM\Column(type: 'boolean')]
    private bool $public;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $import = null;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTime $createdAt = null;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
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

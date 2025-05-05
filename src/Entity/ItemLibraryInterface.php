<?php

namespace Aropixel\AdminBundle\Entity;

use Symfony\Component\HttpFoundation\File\File;

interface ItemLibraryInterface
{
    public function getFilename(): ?string;

    public function getTempPath(): ?string;

    public function getFile(): ?File;

    public function setTitle(string $title): self;

    public function setFilename(string $filename): self;

    public function setExtension(string $extension): self;

    public function getCategory(): string;

    public function setCategory(string $category): void;

}

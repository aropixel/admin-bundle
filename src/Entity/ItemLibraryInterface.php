<?php

namespace Aropixel\AdminBundle\Entity;

use Symfony\Component\HttpFoundation\File\File;

interface ItemLibraryInterface
{
    public function getFilename(): ?string;

    public function getTempPath(): ?string;

    public function getWebPath(): ?string;

    public function getFile(): ?File;
}

<?php

namespace Aropixel\AdminBundle\Entity;

interface AttachedFileInterface
{
    public function getFile(): ?FileInterface;

    public function getFilename(): ?string;
}

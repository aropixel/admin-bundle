<?php

namespace Aropixel\AdminBundle\Entity;

interface AttachedImageInterface
{
    public function getImage(): ?ImageInterface;

    public function getFilename(): ?string;
}

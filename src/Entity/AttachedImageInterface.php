<?php

namespace Aropixel\AdminBundle\Entity;

interface AttachedImageInterface
{
    public function getImage(): ?ImageInterface;
    public function setImage(?ImageInterface $image): void;
    public function getFilename() : ?string;
    public function getTitle(): ?string;
}

<?php

namespace Aropixel\AdminBundle\Entity;

interface ImageInterface extends ItemLibraryInterface
{
    public function getWidth(): ?int;

    public function getHeight(): ?int;
}

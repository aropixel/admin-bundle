<?php

namespace Aropixel\AdminBundle\Entity;

use Aropixel\AdminBundle\Entity\ItemLibraryInterface;

interface ImageInterface extends ItemLibraryInterface
{
    public function getWidth(): ?int;
    public function getHeight(): ?int;
}

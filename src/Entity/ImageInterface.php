<?php

namespace Aropixel\AdminBundle\Entity;

interface ImageInterface extends ItemLibraryInterface
{
    public function setWidth(int $width): void;
    public function getWidth(): ?int;

    public function setHeight(int $height): void;
    public function getHeight(): ?int;
}

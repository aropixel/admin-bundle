<?php

namespace Aropixel\AdminBundle\Entity;

interface CropInterface
{
    public function getImage();

    public function getFilter(): string;

    public function getCrop(): ?string;
}

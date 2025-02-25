<?php

namespace Aropixel\AdminBundle\Entity;

interface CroppableInterface extends AttachedImageInterface
{
    public function getImageUid(): string;

    public function getCropsInfos(): array;
}

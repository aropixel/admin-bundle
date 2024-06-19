<?php

namespace Aropixel\AdminBundle\Entity;

use Aropixel\AdminBundle\Entity\AttachedImageInterface;

interface CroppableInterface extends AttachedImageInterface
{
    public function getImageUid(): string;

    public function getCropsInfos(): array;
}

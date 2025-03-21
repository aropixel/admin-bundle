<?php

namespace Aropixel\AdminBundle\Entity;

interface CroppableInterface extends AttachedImageInterface
{
    public function getImageUid(): string;

    /**
     * @return array<mixed>
     */
    public function getCropsInfos(): array;
}

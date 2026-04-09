<?php

namespace Aropixel\AdminBundle\Component\Media\Image\Editor;

use Aropixel\AdminBundle\Entity\Image;

interface EditorImageBuilderInterface
{
    public function buildImageUrl(Image $image, ?string $width = null, ?string $filter = null): string;
}

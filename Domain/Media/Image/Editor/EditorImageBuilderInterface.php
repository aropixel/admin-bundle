<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 17/02/2023 à 18:24
 */

namespace Aropixel\AdminBundle\Domain\Media\Image\Editor;

use Aropixel\AdminBundle\Entity\Image;

interface EditorImageBuilderInterface
{
    public function buildImageUrl(Image $image, ?string $width=null, ?string $filter=null) : string;
}

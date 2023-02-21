<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 21/02/2023 à 14:02
 */

namespace Aropixel\AdminBundle\Domain\Media\Image\Library\Factory;

use Aropixel\AdminBundle\Entity\ItemLibraryInterface;

interface ImageFactoryInterface
{
    public function create() : ItemLibraryInterface;
}

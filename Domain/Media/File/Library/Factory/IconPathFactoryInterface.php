<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 09/03/2023 à 17:20
 */

namespace Aropixel\AdminBundle\Domain\Media\File\Library\Factory;

interface IconPathFactoryInterface
{
    public function getIconPath(string $extension) : string;
}

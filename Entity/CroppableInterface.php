<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 13/02/2023 à 10:25
 */

namespace Aropixel\AdminBundle\Entity;

use Aropixel\AdminBundle\Entity\ImageInterface;

interface CroppableInterface extends ImageInterface
{
    public function getImageUid() : string;
    public function getCropsInfos() : array;
}

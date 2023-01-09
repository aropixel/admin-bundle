<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 24/10/2019 à 11:40
 */

namespace Aropixel\AdminBundle\Domain\Entity;


interface CropInterface
{

    public function getImage();

    public function getFilter();

    public function getCrop();

}

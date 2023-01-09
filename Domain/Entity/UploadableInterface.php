<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 24/10/2019 à 11:40
 */

namespace Aropixel\AdminBundle\Domain\Entity;


interface UploadableInterface
{

    public function preUpload();

    public function upload();

    public function removeUpload();

}

<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 24/10/2019 à 11:40
 */

namespace Aropixel\AdminBundle\Entity;


use Symfony\Component\HttpFoundation\File\File;

interface ItemLibraryInterface
{

    public function getFilename() : ?string;

    public function getTempPath() : ?string;

    public function getFile() : ?File;

}

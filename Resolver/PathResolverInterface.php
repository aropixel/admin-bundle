<?php
/**
 * Créé par Aropixel @2021.
 * Par: Joël Gomez Caballe
 * Date: 08/03/2021 à 16:18
 */

namespace Aropixel\AdminBundle\Resolver;

interface PathResolverInterface
{
    public function getAbsoluteDirectory($directory);

    public function getAbsolutePath($directory, $fileName);

    public function getDataRootRelativePath($directory, $fileName);

    public function fileExists($directory, $fileName);
}

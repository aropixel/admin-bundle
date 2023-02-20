<?php
/**
 * Créé par Aropixel @2021.
 * Par: Joël Gomez Caballe
 * Date: 08/03/2021 à 16:18
 */

namespace App\Aropixel\AdminBundle\Domain\Media\Resolver;

interface PathResolverInterface
{
    public function getPublicAbsolutePath(string $fileName, ?string $directory=null) : string;

    public function getPrivateAbsolutePath(string $fileName, ?string $directory=null) : string;

    public function publicFileExists(string $fileName, ?string $directory=null) : bool;

    public function privateFileExists(string $fileName, ?string $directory=null) : bool;
}

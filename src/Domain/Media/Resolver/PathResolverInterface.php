<?php

namespace Aropixel\AdminBundle\Domain\Media\Resolver;

interface PathResolverInterface
{
    public function getPublicAbsolutePath(string $fileName, ?string $directory = null): string;

    public function getPrivateAbsolutePath(string $fileName, ?string $directory = null): string;

    public function publicFileExists(string $fileName, ?string $directory = null): bool;

    public function privateFileExists(string $fileName, ?string $directory = null): bool;
}

<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Resolver;

use Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class PathResolver implements PathResolverInterface
{
    public const PRIVATE_DIR = 'private';
    public const PUBLIC_DIR = 'public';

    /**
     * PathResolver constructor.
     */
    public function __construct(
        private readonly KernelInterface $kernel
    ) {
    }

    public function getPublicAbsolutePath(string $fileName, ?string $directory = null): string
    {
        $path = $this->kernel->getProjectDir();
        $path .= '/' . self::PUBLIC_DIR;

        if (null !== $directory) {
            $path .= '/' . $directory;
        }

        $path .= '/' . $fileName;

        return $path;
    }

    public function getPrivateAbsolutePath(string $fileName, ?string $directory = null): string
    {
        $path = $this->kernel->getProjectDir();
        $path .= '/' . self::PRIVATE_DIR;

        if (null !== $directory) {
            $path .= '/' . $directory;
        }

        $path .= '/' . $fileName;

        return $path;
    }

    public function publicFileExists(string $fileName, ?string $directory = null): bool
    {
        return file_exists($this->getPublicAbsolutePath($fileName, $directory));
    }

    public function privateFileExists(string $fileName, ?string $directory = null): bool
    {
        return file_exists($this->getPrivateAbsolutePath($fileName, $directory));
    }
}

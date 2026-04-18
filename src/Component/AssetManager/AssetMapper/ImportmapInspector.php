<?php

namespace Aropixel\AdminBundle\Component\AssetManager\AssetMapper;

use Symfony\Component\HttpKernel\KernelInterface;

class ImportmapInspector
{
    /**
     * @param array<string, array<mixed>> $importmap
     */
    public function __construct(
        private readonly KernelInterface $kernel,
    ) {
    }

    public function isEnabled(): bool
    {
        return file_exists($this->kernel->getProjectDir() . '/importmap.php');
    }
}

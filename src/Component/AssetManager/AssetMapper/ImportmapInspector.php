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
        private array $importmap = []
    ) {
        $importmapPath = $this->kernel->getProjectDir() . '/importmap.php';

        if (file_exists($importmapPath)) {
            $this->importmap = include $importmapPath;
        }
    }

    public function hasEntry(string $entry): bool
    {
        return isset($this->importmap[$entry]);
    }
}

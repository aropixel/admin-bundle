<?php

namespace Aropixel\AdminBundle\Infrastructure\AssetManager\AssetMapper;

use Symfony\Component\HttpKernel\KernelInterface;

class ImportmapInspector
{
    public function __construct(
        private readonly KernelInterface $kernel,
        private array $importmap = []
    ) {
        $importmapPath = $this->kernel->getProjectDir().'/config/importmap.php';

        if (file_exists($importmapPath)) {
            $this->importmap = include $importmapPath;
        }
    }

    public function hasEntry(string $entry): bool
    {
        return isset($this->importmap[$entry]);
    }

}
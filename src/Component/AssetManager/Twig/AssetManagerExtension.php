<?php

namespace Aropixel\AdminBundle\Component\AssetManager\Twig;

use Aropixel\AdminBundle\Component\AssetManager\AssetMapper\ImportmapInspector;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AssetManagerExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private ImportmapInspector $importmapInspector,
    ) {}

    public function getGlobals(): array
    {
        return [
            'aropixel_admin_entry_available' => $this->importmapInspector->hasEntry('@aropixel/admin-bundle'),
        ];
    }
}
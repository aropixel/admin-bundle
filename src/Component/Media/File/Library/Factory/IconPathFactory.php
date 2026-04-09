<?php

namespace Aropixel\AdminBundle\Component\Media\File\Library\Factory;

use Symfony\Component\HttpKernel\KernelInterface;

class IconPathFactory implements IconPathFactoryInterface
{
    public function __construct(
        private readonly KernelInterface $kernel
    ) {
    }

    public function getIconPath(string $extension): string
    {
        $iconExt = 'img/files/' . $extension . '.png';
        $iconDft = 'img/files/file.png';

        $path = $this->kernel->getProjectDir();
        $path .= '/public/';

        return '/bundles/aropixeladmin/' . (file_exists($path . '/bundles/aropixeladmin/' . $iconExt) ? $iconExt : $iconDft);
    }
}

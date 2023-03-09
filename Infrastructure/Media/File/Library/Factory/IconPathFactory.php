<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 09/03/2023 à 17:21
 */

namespace Aropixel\AdminBundle\Infrastructure\Media\File\Library\Factory;

use Aropixel\AdminBundle\Domain\Media\File\Library\Factory\IconPathFactoryInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class IconPathFactory implements IconPathFactoryInterface
{
    private KernelInterface $kernel;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }


    public function getIconPath(string $extension): string
    {
        $iconExt = "img/files/".$extension.".png";
        $iconDft = "img/files/file.png";

        $path = $this->kernel->getProjectDir();
        $path.= '/public/';

        return '/bundles/aropixeladmin/'.(file_exists($path.'/bundles/aropixeladmin/'.$iconExt) ? $iconExt : $iconDft);
    }
}

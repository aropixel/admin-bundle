<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 11/08/2020 à 16:30
 */

namespace Aropixel\AdminBundle\Image;


use Aropixel\AdminBundle\Entity\Image;
use Symfony\Component\HttpKernel\KernelInterface;

class PathResolver
{

    /** @var KernelInterface */
    private $kernel;

    /**
     * PathResolver constructor.
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }


    public function getAbsoluteDirectory()
    {
        $path = $this->kernel->getProjectDir();
        $path.= '/'.Image::PRIVATE_DIR.'/'.Image::UPLOAD_DIR;

        return $path;
    }


    public function getAbsolutePath($fileName)
    {
        $path = $this->kernel->getProjectDir();
        $path.= '/'.Image::PRIVATE_DIR.'/'.Image::UPLOAD_DIR;
        $path.= '/'.$fileName;

        return $path;
    }


    public function getDataRootRelativePath($fileName)
    {
        return Image::UPLOAD_DIR.'/'.$fileName;
    }


    public function fileExists($fileName)
    {
        return file_exists($this->getAbsolutePath($fileName));
    }


}

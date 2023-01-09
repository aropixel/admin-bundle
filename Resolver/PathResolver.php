<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 11/08/2020 à 16:30
 */

namespace Aropixel\AdminBundle\Resolver;


use Symfony\Component\HttpKernel\KernelInterface;

class PathResolver implements PathResolverInterface
{
    //
    const PRIVATE_DIR = 'private';


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


    public function getAbsoluteDirectory($directory)
    {
        $path = $this->kernel->getProjectDir();
        $path.= '/'.self::PRIVATE_DIR.'/'.$directory;

        return $path;
    }


    public function getAbsolutePath($directory, $fileName)
    {
        $path = $this->kernel->getProjectDir();
        $path.= '/'.self::PRIVATE_DIR.'/'.$directory;
        $path.= '/'.$fileName;

        return $path;
    }


    public function getDataRootRelativePath($directory, $fileName)
    {
        return $directory.'/'.$fileName;
    }


    public function fileExists($directory, $fileName)
    {
        return file_exists($this->getAbsolutePath($directory, $fileName));
    }


}

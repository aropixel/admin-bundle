<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 11/08/2020 à 16:30
 */

namespace Aropixel\AdminBundle\Infrastructure\Media\Resolver;


use App\Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class PathResolver implements PathResolverInterface
{
    //
    const PRIVATE_DIR = 'private';
    const PUBLIC_DIR = 'public';


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


    public function getPublicAbsolutePath(string $fileName, ?string $directory = null): string
    {
        $path = $this->kernel->getProjectDir();
        $path.= '/'.self::PUBLIC_DIR;

        if (!is_null($directory)) {
            $path.= '/'.$directory;
        }

        $path.= '/'.$fileName;
        return $path;
    }


    public function getPrivateAbsolutePath(string $fileName, ?string $directory = null): string
    {
        $path = $this->kernel->getProjectDir();
        $path.= '/'.self::PRIVATE_DIR;

        if (!is_null($directory)) {
            $path.= '/'.$directory;
        }

        $path.= '/'.$fileName;
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

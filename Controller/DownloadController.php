<?php

namespace Aropixel\AdminBundle\Controller;

use Aropixel\AdminBundle\Domain\Entity\File;
use Aropixel\AdminBundle\Resolver\PathResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class DownloadController extends AbstractController
{
    /** @var PathResolverInterface */
    private $pathResolver;


    /**
     * @param PathResolverInterface $pathResolver
     */
    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    /**
     * Upload a File.
     *
     * @Route("/download/{id}/{filename}", name="file_download", methods={"GET"})
     */
    public function downloadAction(File $file)
    {
        $path = $this->pathResolver->getAbsolutePath(File::UPLOAD_DIR, $file->getFilename());
        return $this->file($path, $file->getRewritedFileName());
    }


}

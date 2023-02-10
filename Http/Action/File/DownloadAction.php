<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Entity\File;
use Aropixel\AdminBundle\Resolver\PathResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class DownloadAction extends AbstractController
{
    private PathResolverInterface $pathResolver;

    /**
     * @param PathResolverInterface $pathResolver
     */
    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    /**
     * Upload a file.
     */
    public function __invoke(File $file) : Response
    {

        $path = $this->pathResolver->getAbsolutePath(File::UPLOAD_DIR, $file->getFilename());
        return $this->file($path, $file->getRewritedFileName());

    }


}

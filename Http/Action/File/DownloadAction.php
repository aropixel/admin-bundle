<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Entity\File;
use Aropixel\AdminBundle\Infrastructure\Media\Resolver\PathResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DownloadAction extends AbstractController
{
    private PathResolverInterface $pathResolver;

    /**
     * @param \Aropixel\AdminBundle\Infrastructure\Media\Resolver\PathResolverInterface $pathResolver
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

        $path = $this->pathResolver->getPrivateAbsolutePath($file->getFilename(), File::UPLOAD_DIR);
        return $this->file($path, $file->getRewritedFileName());

    }


}


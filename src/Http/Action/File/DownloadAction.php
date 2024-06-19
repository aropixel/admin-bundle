<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DownloadAction extends AbstractController
{
    public function __construct(
        private readonly PathResolverInterface $pathResolver
    ) {
    }

    /**
     * Upload a file.
     */
    public function __invoke(File $file): Response
    {
        $path = $this->pathResolver->getPrivateAbsolutePath($file->getFilename(), File::UPLOAD_DIR);

        return $this->file($path, $file->getRewrittenFileName());
    }
}

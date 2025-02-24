<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\File;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadAction extends AbstractController
{
    public function __construct(
        private readonly FilesystemOperator $privateStorage,
        private readonly PathResolverInterface $pathResolver,
    ) {
    }

    public function __invoke(File $file): Response
    {
        $stream = $this->privateStorage->readStream($this->pathResolver->getFilePath($file));

        return new StreamedResponse(
            function () use ($stream) {
                fpassthru($stream);
            },
            Response::HTTP_OK,
            [
                'Content-Transfer-Encoding', 'binary',
                'Content-Type' => $file->getFile()->getMimeType(),
                'Content-Disposition' => 'attachment; filename="'.$file->getFilename().'"',
                'Content-Length' => fstat($stream)['size'],
            ]
        );
    }
}

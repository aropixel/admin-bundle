<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Component\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\File;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mime\MimeTypes;

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

        $properties = [
            'Content-Transfer-Encoding', 'binary',
            'Content-Disposition' => 'attachment; filename="' . $file->getFilename() . '"',
            'Content-Length' => fstat($stream)['size'],
        ];

        $mimeTypes = new MimeTypes();
        $mimes = $mimeTypes->getMimeTypes($file->getExtension());
        if (\count($mimes) > 0) {
            $properties['Content-Type'] = $mimes[0];
        }

        return new StreamedResponse(
            function () use ($stream) {
                fpassthru($stream);
            },
            Response::HTTP_OK,
            $properties
        );
    }
}

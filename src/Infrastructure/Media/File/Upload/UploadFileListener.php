<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\File\Upload;

use Aropixel\AdminBundle\Entity\File;
use Aropixel\AdminBundle\Entity\FileInterface;
use Aropixel\AdminBundle\Infrastructure\Media\PreUploadHandler;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;

class UploadFileListener
{
    public function __construct(
        private readonly FilesystemOperator $privateStorage,
        private readonly PreUploadHandler $preUploadHandler,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function prePersist(FileInterface $file): void
    {
        $this->preUploadHandler->handlePreUpload($file);
    }

    public function postPersist(FileInterface $file): void
    {
        if (null === $file->getFile()) {
            return;
        }

        try {
            $this->privateStorage->write(File::UPLOAD_DIR.'/'.$file->getFilename(), file_get_contents($file->getFile()->getPathname()));
            unlink($file->getFile()->getPathname());
        }
        catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
        }
    }

    public function postRemove(FileInterface $file): void
    {
        try {
            $this->privateStorage->delete(File::UPLOAD_DIR.'/'.$file->getFilename());
        }
        catch (\Throwable) {}
    }

}

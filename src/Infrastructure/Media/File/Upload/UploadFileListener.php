<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\File\Upload;

use Aropixel\AdminBundle\Entity\File;
use Aropixel\AdminBundle\Entity\FileInterface;
use Aropixel\AdminBundle\Infrastructure\Media\PreUploadHandler;
use Aropixel\AdminBundle\Infrastructure\Media\Resolver\PathResolver;

class UploadFileListener
{
    public function __construct(
        private readonly PathResolver $pathResolver,
        private readonly PreUploadHandler $preUploadHandler
    ) {
    }

    public function prePersist(FileInterface $file): void
    {
        $this->preUpload($file);
    }

    public function preUpdate(FileInterface $file): void
    {
        $this->preUpload($file);
    }

    public function postPersist(FileInterface $file): void
    {
        $this->upload($file);
    }

    public function postUpdate(FileInterface $file): void
    {
        $this->upload($file);
    }

    public function postRemove(FileInterface $file): void
    {
        $file = $this->pathResolver->getPrivateAbsolutePath($file->getFilename(), File::UPLOAD_DIR);
        if ($file && file_exists($file)) {
            unlink($file);
        }
    }

    private function preUpload(FileInterface $file): void
    {
        $this->preUploadHandler->handlePreUpload($file);
    }

    private function upload(FileInterface $file): void
    {
        if (null === $file->getFile()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $file->getFile()->move($this->pathResolver->getPrivateAbsolutePath(File::UPLOAD_DIR), $file->getFilename());

        // check if we have an old image
        if ($file->getTempPath()) {
            // delete the old image
            unlink($this->pathResolver->getPrivateAbsolutePath(File::UPLOAD_DIR) . '/' . $file->getTempPath());
        }
    }
}

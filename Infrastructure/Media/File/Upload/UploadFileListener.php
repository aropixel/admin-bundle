<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 12/08/2020 à 13:55
 */

namespace Aropixel\AdminBundle\Infrastructure\Media\File\Upload;

use Aropixel\AdminBundle\Entity\File;
use Aropixel\AdminBundle\Entity\FileInterface;
use Aropixel\AdminBundle\Infrastructure\Media\PreUploadHandler;
use Aropixel\AdminBundle\Resolver\PathResolver;

class UploadFileListener
{

    private PathResolver $pathResolver;
    private PreUploadHandler $preUploadHandler;


    /**
     * @param PathResolver $pathResolver
     * @param PreUploadHandler $preUploadHandler
     */
    public function __construct(PathResolver $pathResolver, PreUploadHandler $preUploadHandler)
    {
        $this->pathResolver = $pathResolver;
        $this->preUploadHandler = $preUploadHandler;
    }


    public function prePersist(FileInterface $file) : void
    {
        $this->preUpload($file);
    }

    public function preUpdate(FileInterface $file) : void
    {
        $this->preUpload($file);
    }

    public function postPersist(FileInterface $file) : void
    {
        $this->upload($file);
    }

    public function postUpdate(FileInterface $file) : void
    {
        $this->upload($file);
    }

    public function postRemove(FileInterface $file) : void
    {
        $file = $this->pathResolver->getAbsolutePath(File::UPLOAD_DIR, $file->getFilename());
        if ($file && file_exists($file)) {
            unlink($file);
        }
    }


    /**
     */
    private function preUpload(FileInterface $file) : void
    {
        $this->preUploadHandler->handlePreUpload($file);
    }


    private function upload(FileInterface $file) : void
    {
        if (null === $file->getFile()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $file->getFile()->move($this->pathResolver->getAbsoluteDirectory(File::UPLOAD_DIR), $file->getFilename());

        // check if we have an old image
        if ($file->getTempPath()) {
            // delete the old image
            unlink($this->pathResolver->getAbsoluteDirectory(File::UPLOAD_DIR).'/'.$file->getTempPath());
        }
    }


}

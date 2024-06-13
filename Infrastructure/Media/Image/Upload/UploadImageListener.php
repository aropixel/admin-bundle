<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 12/08/2020 à 13:55
 */

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Upload;

use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Entity\ItemLibraryInterface;
use Aropixel\AdminBundle\Infrastructure\Media\PreUploadHandler;
use Aropixel\AdminBundle\Infrastructure\Media\Resolver\PathResolver;

class UploadImageListener
{

    private PathResolver $pathResolver;
    private PreUploadHandler $preUploadHandler;


    /**
     * @param \AdminBundle\Infrastructure\Media\Resolver\PathResolver $pathResolver
     * @param PreUploadHandler $preUploadHandler
     */
    public function __construct(PathResolver $pathResolver, PreUploadHandler $preUploadHandler)
    {
        $this->pathResolver = $pathResolver;
        $this->preUploadHandler = $preUploadHandler;
    }


    public function prePersist(ItemLibraryInterface $image) : void
    {
        $this->preUpload($image);
    }

    public function preUpdate(ItemLibraryInterface $image) : void
    {
        $this->preUpload($image);
    }

    public function postPersist(ItemLibraryInterface $image) : void
    {
        $this->upload($image);
    }

    public function postUpdate(ItemLibraryInterface $image) : void
    {
        $this->upload($image);
    }

    public function postRemove(ItemLibraryInterface $image) : void
    {
        $file = $this->pathResolver->getPrivateAbsolutePath($image->getFilename(), Image::UPLOAD_DIR);
        if ($file && file_exists($file)) {
            unlink($file);
        }
    }


    private function preUpload(ItemLibraryInterface $image) : void
    {
        $this->preUploadHandler->handlePreUpload($image);
    }

    /**
     */
    private function upload(ItemLibraryInterface $image) : void
    {
        if (null === $image->getFile()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $image->getFile()->move($this->pathResolver->getPrivateAbsolutePath(Image::UPLOAD_DIR), $image->getFilename());

        // check if we have an old image
        if ($image->getTempPath()) {
            // delete the old image
            unlink($this->pathResolver->getPrivateAbsolutePath(Image::UPLOAD_DIR).'/'.$image->getTempPath());
        }
    }


}

<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 12/08/2020 à 13:55
 */

namespace Aropixel\AdminBundle\EventListener;

use Aropixel\AdminBundle\Domain\Entity\Image;
use Aropixel\AdminBundle\Domain\Entity\ImageInterface;
use Aropixel\AdminBundle\Resolver\PathResolver;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UploadImageListener
{

    /** @var PathResolver */
    private $pathResolver;

    /**
     * UploadListener constructor.
     * @param PathResolver $pathResolver
     */
    public function __construct(PathResolver $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    public function prePersist(ImageInterface $image, LifecycleEventArgs $event)
    {
        $this->preUpload($image);
    }

    public function preUpdate(ImageInterface $image, LifecycleEventArgs $event)
    {
        $this->preUpload($image);
    }

    public function postPersist(ImageInterface $image, LifecycleEventArgs $event)
    {
        $this->upload($image);
    }

    public function postUpdate(ImageInterface $image, LifecycleEventArgs $event)
    {
        $this->upload($image);
    }

    public function postRemove(ImageInterface $image, LifecycleEventArgs $event)
    {
        $file = $this->pathResolver->getAbsolutePath(Image::UPLOAD_DIR, $image->getFilename());
        if ($file && file_exists($file)) {
            unlink($file);
        }
    }


    /**
     */
    private function preUpload(ImageInterface $image)
    {
        $this->createdAt = $this->updatedAt = new \DateTime();

        if (null !== $image->getFile()) {

            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $filename.= '.'.$image->getFile()->guessExtension();
            $image->setFilename($filename);

            //
            $image->setExtension($image->getFile()->guessExtension());

            //
            $i = strrpos($image->getTitre(), '.');
            if ($i!==false) {
                $image->setTitre(substr($image->getTitre(), 0, $i));
            }

        }
    }

    /**
     */
    private function upload(ImageInterface $image)
    {
        if (null === $image->getFile()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $image->getFile()->move($this->pathResolver->getAbsoluteDirectory(Image::UPLOAD_DIR), $image->getFilename());

        // check if we have an old image
        if ($image->getTempPath()) {
            // delete the old image
            unlink($this->pathResolver->getAbsoluteDirectory(Image::UPLOAD_DIR).'/'.$image->getTempPath());
            // clear the temp image path
            $this->temp = null;
        }
        $this->file = null;
    }


}

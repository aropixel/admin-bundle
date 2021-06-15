<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 12/08/2020 à 13:55
 */

namespace Aropixel\AdminBundle\EventListener;

use Aropixel\AdminBundle\Entity\File;
use Aropixel\AdminBundle\Entity\FileInterface;
use Aropixel\AdminBundle\Resolver\PathResolver;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UploadFileListener
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

    public function prePersist(FileInterface $file, LifecycleEventArgs $event)
    {
        $this->preUpload($file);
    }

    public function preUpdate(FileInterface $file, LifecycleEventArgs $event)
    {
        $this->preUpload($file);
    }

    public function postPersist(FileInterface $file, LifecycleEventArgs $event)
    {
        $this->upload($file);
    }

    public function postUpdate(FileInterface $file, LifecycleEventArgs $event)
    {
        $this->upload($file);
    }

    public function postRemove(FileInterface $file, LifecycleEventArgs $event)
    {
        $file = $this->pathResolver->getAbsolutePath(File::UPLOAD_DIR, $file->getFilename());
        if ($file && file_exists($file)) {
            unlink($file);
        }
    }


    /**
     */
    private function preUpload(FileInterface $file)
    {
        $this->createdAt = $this->updatedAt = new \DateTime();

        if (null !== $file->getFile()) {

            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $filename.= '.'.$file->getFile()->guessExtension();
            $file->setFilename($filename);

            //
            $file->setExtension($file->getFile()->guessExtension());

            //
            $i = strrpos($file->getTitre(), '.');
            if ($i!==false) {
                $file->setTitre(substr($file->getTitre(), 0, $i));
            }

        }
    }

    /**
     */
    private function upload(FileInterface $file)
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
            // clear the temp image path
            $this->temp = null;
        }
        $this->file = null;
    }


}

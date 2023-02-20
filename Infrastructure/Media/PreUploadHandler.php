<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 10/02/2023 à 14:16
 */

namespace Aropixel\AdminBundle\Infrastructure\Media;

use Aropixel\AdminBundle\Entity\ItemLibraryInterface;

class PreUploadHandler
{
    public function handlePreUpload(ItemLibraryInterface $media)
    {
        $now = new \DateTime();
        $media->setCreatedAt($now);
        $media->setUpdatedAt($now);

        if (null !== $media->getFile()) {

            $ext = $media->getFile()->guessExtension();
            $media->setExtension($ext);

            // give a random name to the uploaded file
            $filename = sha1(uniqid(mt_rand(), true)) . '.' . $ext;
            $media->setFilename($filename);

            //
            $i = strrpos($media->getTitle(), '.');
            if ($i!==false) {
                $media->setTitle(substr($media->getTitle(), 0, $i));
            }

        }
    }
}

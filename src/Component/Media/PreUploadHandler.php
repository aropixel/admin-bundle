<?php

namespace Aropixel\AdminBundle\Component\Media;

use Aropixel\AdminBundle\Entity\File;
use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Entity\ItemLibraryInterface;

class PreUploadHandler
{
    public function handlePreUpload(ItemLibraryInterface $media): void
    {
        /** @var Image|File $media */
        $now = new \DateTime();
        $media->setCreatedAt($now);
        $media->setUpdatedAt($now);

        if (null !== $media->getFile()) {
            $ext = $media->getFile()->guessExtension();
            $media->setExtension($ext);

            // give a random name to the uploaded file
            $filename = sha1(uniqid((string) mt_rand(), true)) . '.' . $ext;
            $media->setFilename($filename);

            $i = mb_strrpos($media->getTitle(), '.');
            if (false !== $i) {
                $media->setTitle(mb_substr($media->getTitle(), 0, $i));
            }
        }
    }
}

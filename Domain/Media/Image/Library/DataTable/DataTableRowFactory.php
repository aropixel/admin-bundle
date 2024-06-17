<?php

namespace Aropixel\AdminBundle\Domain\Media\Image\Library\DataTable;

use Aropixel\AdminBundle\Domain\DataTable\DataTableRowFactoryInterface;
use Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\Image;
use Twig\Environment;

class DataTableRowFactory implements DataTableRowFactoryInterface
{
    public function __construct(
        private readonly PathResolverInterface $pathResolver,
        private readonly Environment $twig
    ) {
    }

    public function createRow($subject): array
    {
        /** @var Image $image */
        $image = $subject;
        $imagePath = $this->pathResolver->getPrivateAbsolutePath($image->getFilename(), Image::UPLOAD_DIR);

        $bytes = @filesize($imagePath);
        $sz = 'bkMGTP';
        $factor = (int) floor((mb_strlen($bytes) - 1) / 3);
        $decimals = 2;
        $unite = @$sz[$factor];
        if ('b' == $unite || 'k' == $unite) {
            $decimals = 0;
        }
        $filesize = sprintf("%.{$decimals}f", $bytes / 1024 ** $factor) . @$sz[$factor];
        [$width, $height] = getimagesize($imagePath);

        return [$this->twig->render('@AropixelAdmin/Image/Datatabler/checkbox.html.twig', ['image' => $image]), $this->twig->render('@AropixelAdmin/Image/Datatabler/preview.html.twig', ['image' => $image]), $this->twig->render('@AropixelAdmin/Image/Datatabler/title.html.twig', ['image' => $image]), $image->getCreatedAt()->format('d/m/Y'), $this->twig->render('@AropixelAdmin/Image/Datatabler/properties.html.twig', ['image' => $image, 'filesize' => $filesize, 'width' => $width, 'height' => $height]), $this->twig->render('@AropixelAdmin/Image/Datatabler/button.html.twig', ['image' => $image])];
    }
}

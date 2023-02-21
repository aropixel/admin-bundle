<?php

namespace Aropixel\AdminBundle\Domain\Media\Image\Library\DataTable;

use Aropixel\AdminBundle\Domain\DataTable\DataTableRowFactoryInterface;
use Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\Image;
use Twig\Environment;

class DataTableRowFactory implements DataTableRowFactoryInterface
{

    private PathResolverInterface $pathResolver;
    private Environment $twig;

    /**
     * @param PathResolverInterface $pathResolver
     * @param Environment $twig
     */
    public function __construct(PathResolverInterface $pathResolver, Environment $twig)
    {
        $this->pathResolver = $pathResolver;
        $this->twig = $twig;
    }


    public function createRow($subject): array
    {
        /** @var Image $image */
        $image = $subject;
        $imagePath = $this->pathResolver->getPrivateAbsolutePath($image->getFilename(), Image::UPLOAD_DIR);

        $bytes = @filesize($imagePath);
        $sz = 'bkMGTP';
        $factor = (int)floor((strlen($bytes) - 1) / 3);
        $decimals = 2;
        $unite = @$sz[$factor];
        if ($unite=='b' || $unite=='k') {
            $decimals = 0;
        }
        $filesize = sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
        list($width, $height) = getimagesize($imagePath);

        return array(
            $this->twig->render('@AropixelAdmin/Image/Datatabler/checkbox.html.twig', array('image' => $image)),
            $this->twig->render('@AropixelAdmin/Image/Datatabler/preview.html.twig', array('image' => $image)),
            $this->twig->render('@AropixelAdmin/Image/Datatabler/title.html.twig', array('image' => $image)),
            $image->getCreatedAt()->format('d/m/Y'),
            $this->twig->render('@AropixelAdmin/Image/Datatabler/properties.html.twig', array('image' => $image, 'filesize' => $filesize, 'width' => $width, 'height' => $height)),
            $this->twig->render('@AropixelAdmin/Image/Datatabler/button.html.twig', array('image' => $image))
        );

    }
}

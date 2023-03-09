<?php

namespace Aropixel\AdminBundle\Domain\Media\File\Library\DataTable;

use Aropixel\AdminBundle\Domain\DataTable\DataTableRowFactoryInterface;
use Aropixel\AdminBundle\Domain\Media\File\Library\Factory\IconPathFactoryInterface;
use Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\File;
use Twig\Environment;

class DataTableRowFactory implements DataTableRowFactoryInterface
{

    private IconPathFactoryInterface $iconPathFactory;
    private PathResolverInterface $pathResolver;
    private Environment $twig;

    /**
     * @param IconPathFactoryInterface $iconPathFactory
     * @param PathResolverInterface $pathResolver
     * @param Environment $twig
     */
    public function __construct(IconPathFactoryInterface $iconPathFactory, PathResolverInterface $pathResolver, Environment $twig)
    {
        $this->iconPathFactory = $iconPathFactory;
        $this->pathResolver = $pathResolver;
        $this->twig = $twig;
    }


    public function createRow($subject): array
    {
        /** @var File $file */
        $file = $subject;
        $filePath = $this->pathResolver->getPrivateAbsolutePath($file->getFilename(), File::UPLOAD_DIR);

        $bytes = @filesize($filePath);
        $sz = 'bkMGTP';
        $factor = (int)floor((strlen($bytes) - 1) / 3);
        $decimals = 2;
        $unite = @$sz[$factor];
        if ($unite=='b' || $unite=='k') {
            $decimals = 0;
        }
        $filesize = sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
        list($width, $height) = getimagesize($filePath);

        $icon = $this->iconPathFactory->getIconPath($subject->getExtension());

        return [
            $this->twig->render('@AropixelAdmin/File/Datatabler/checkbox.html.twig', ['file' => $file]),
            $this->twig->render('@AropixelAdmin/File/Datatabler/preview.html.twig', ['file' => $file, 'icon' => $icon]),
            $this->twig->render('@AropixelAdmin/File/Datatabler/title.html.twig', ['file' => $file]),
            $file->getCreatedAt()->format('d/m/Y'),
            $this->twig->render('@AropixelAdmin/File/Datatabler/properties.html.twig', ['file' => $file, 'filesize' => $filesize, 'width' => $width, 'height' => $height]),
            $this->twig->render('@AropixelAdmin/File/Datatabler/button.html.twig', ['file' => $file])
        ];

    }
}

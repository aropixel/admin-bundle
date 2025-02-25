<?php

namespace Aropixel\AdminBundle\Domain\Media\File\Library\DataTable;

use Aropixel\AdminBundle\Domain\DataTable\DataTableRowFactoryInterface;
use Aropixel\AdminBundle\Domain\Media\File\Library\Factory\IconPathFactoryInterface;
use Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\File;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Twig\Environment;

class DataTableRowFactory implements DataTableRowFactoryInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly FilesystemOperator $privateStorage,
        private readonly IconPathFactoryInterface $iconPathFactory,
        private readonly PathResolverInterface $pathResolver,
    ) {
    }

    public function createRow($subject): array
    {
        /** @var File $file */
        $file = $subject;

        try {
            $bytes = $this->privateStorage->fileSize($this->pathResolver->getFilePath($file));
        } catch (FilesystemException) {
            $bytes = 0;
        }

        $sz = 'bkMGTP';
        $factor = (int) floor((mb_strlen($bytes) - 1) / 3);
        $decimals = 2;
        $unite = @$sz[$factor];
        if ('b' == $unite || 'k' == $unite) {
            $decimals = 0;
        }
        $filesize = sprintf("%.{$decimals}f", $bytes / 1024 ** $factor) . @$sz[$factor];

        $icon = $this->iconPathFactory->getIconPath($subject->getExtension());

        return [
            $this->twig->render('@AropixelAdmin/File/Datatabler/checkbox.html.twig', ['file' => $file]),
            $this->twig->render('@AropixelAdmin/File/Datatabler/preview.html.twig', ['file' => $file, 'icon' => $icon]),
            $this->twig->render('@AropixelAdmin/File/Datatabler/title.html.twig', ['file' => $file]),
            $file->getCreatedAt()->format('d/m/Y'),
            $this->twig->render('@AropixelAdmin/File/Datatabler/properties.html.twig', ['file' => $file, 'filesize' => $filesize]),
            $this->twig->render('@AropixelAdmin/File/Datatabler/button.html.twig', ['file' => $file]),
        ];
    }
}

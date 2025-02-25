<?php

namespace Aropixel\AdminBundle\Domain\Media\Image\Library\DataTable;

use Aropixel\AdminBundle\Domain\DataTable\DataTableRowFactoryInterface;
use Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class DataTableRowFactory implements DataTableRowFactoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Environment $twig,
        private readonly FilesystemOperator $privateStorage,
        private readonly LoggerInterface $logger,
        private readonly PathResolverInterface $pathResolver,
    ) {
    }

    public function createRow($subject): array
    {
        /** @var Image $image */
        $image = $subject;
        $path = $this->pathResolver->getImagePath($subject);

        try {
            $bytes = $this->privateStorage->fileSize($path);
        } catch (FilesystemException) {
            $bytes = 0;
        }

        $sz = 'bkMGTP';
        $factor = (int) floor((mb_strlen((string)$bytes) - 1) / 3);
        $decimals = 2;
        $unite = @$sz[$factor];
        if ('b' == $unite || 'k' == $unite) {
            $decimals = 0;
        }
        $filesize = sprintf("%.{$decimals}f", $bytes / 1024 ** $factor) . @$sz[$factor];

        $width = $image->getWidth();
        $height = $image->getHeight();
        if (null === $width || null === $height) {
            try {
                $contents = $this->privateStorage->read($path);
                [$width, $height] = getimagesizefromstring($contents);
                $image->setWidth($width);
                $image->setHeight($height);
                $this->em->flush();
            } catch (FilesystemException) {
                $this->logger->error(sprintf('Unable to get image size: %s', $path));
            }
        }

        return [$this->twig->render('@AropixelAdmin/Image/Datatabler/checkbox.html.twig', ['image' => $image]), $this->twig->render('@AropixelAdmin/Image/Datatabler/preview.html.twig', ['image' => $image]), $this->twig->render('@AropixelAdmin/Image/Datatabler/title.html.twig', ['image' => $image]), $image->getCreatedAt()->format('d/m/Y'), $this->twig->render('@AropixelAdmin/Image/Datatabler/properties.html.twig', ['image' => $image, 'filesize' => $filesize, 'width' => $width, 'height' => $height]), $this->twig->render('@AropixelAdmin/Image/Datatabler/button.html.twig', ['image' => $image])];
    }
}

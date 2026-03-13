<?php

namespace Aropixel\AdminBundle\Component\Media\File\Twig;

use Aropixel\AdminBundle\Entity\File;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExtension extends AbstractExtension
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private bool $isLibraryEnabled = false,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('enable_file_library_modal', $this->enableFileLibraryModal(...)),
            new TwigFunction('is_file_library_modal_enabled', $this->isFileLibraryModalEnabled(...)),
            new TwigFunction('aropixel_file_url', $this->getFileUrl(...)),
        ];
    }

    public function enableFileLibraryModal(): void
    {
        $this->isLibraryEnabled = true;
    }

    public function isFileLibraryModalEnabled(): bool
    {
        return $this->isLibraryEnabled;
    }

    public function getFileUrl(File $file): string
    {
        return $this->urlGenerator->generate('file_download', [
            'id' => $file->getId(),
            'filename' => $file->getRewrittenFileName(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

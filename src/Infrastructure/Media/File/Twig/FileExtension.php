<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\File\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExtension extends AbstractExtension
{
    public function __construct(

        private bool $isLibraryEnabled = false,
    ) {
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('enable_file_library_modal', $this->enableFileLibraryModal(...)), new TwigFunction('is_file_library_modal_enabled', $this->isFileLibraryModalEnabled(...))];
    }

    public function enableFileLibraryModal(): void
    {
        $this->isLibraryEnabled = true;
    }

    public function isFileLibraryModalEnabled(): bool
    {
        return $this->isLibraryEnabled;
    }

}

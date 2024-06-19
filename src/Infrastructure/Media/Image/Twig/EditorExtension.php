<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EditorExtension extends AbstractExtension
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_editor_filters', $this->getEditorFilters(...)),
        ];
    }

    public function getEditorFilters(): array
    {
        return $this->parameterBag->get('aropixel_admin.editor_filter_sets');
    }
}

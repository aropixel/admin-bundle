<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 17/02/2023 à 18:31
 */

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EditorExtension extends AbstractExtension
{
    private ParameterBagInterface $parameterBag;

    /**
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_editor_filters', array($this, 'getEditorFilters'))
        ];
    }

    public function getEditorFilters() : array
    {
        return $this->parameterBag->get('aropixel_admin.editor_filter_sets');
    }
}

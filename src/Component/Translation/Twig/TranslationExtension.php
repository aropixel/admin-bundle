<?php

namespace Aropixel\AdminBundle\Component\Translation\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TranslationExtension extends AbstractExtension
{
    public function __construct(
        private readonly ParameterBagInterface $params
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_locales', $this->getLocales(...)),
            new TwigFunction('is_translatable', $this->isTranslatable(...)),
        ];
    }

    /**
     * @return array<string>
     */
    public function getLocales(): array
    {
        return $this->params->get('aropixel_admin.locales');
    }

    public function isTranslatable(): bool
    {
        return 1 < \count($this->params->get('aropixel_admin.locales'));
    }
}

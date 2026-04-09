<?php

namespace Aropixel\AdminBundle\Component\Translation\Resolver;

use Aropixel\AdminBundle\Component\Translation\TranslationResolverInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TranslationResolver implements TranslationResolverInterface
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    public function isTranslatable(): bool
    {
        return 1 < \count($this->parameterBag->get('aropixel_admin.locales'));
    }
}

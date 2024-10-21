<?php

namespace Aropixel\AdminBundle\Infrastructure\Translation\Resolver;

use Aropixel\AdminBundle\Domain\Translation\TranslationResolverInterface;
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

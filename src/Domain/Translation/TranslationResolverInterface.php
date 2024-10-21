<?php

namespace Aropixel\AdminBundle\Domain\Translation;

interface TranslationResolverInterface
{
    public function isTranslatable(): bool;
}

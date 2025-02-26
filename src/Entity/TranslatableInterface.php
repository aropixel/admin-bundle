<?php

namespace Aropixel\AdminBundle\Entity;

use Gedmo\Translatable\Translatable;

interface TranslatableInterface extends Translatable
{
    public function setTranslatableLocale(string $locale): void;
}

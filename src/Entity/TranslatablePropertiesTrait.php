<?php

namespace Aropixel\AdminBundle\Entity;

use Doctrine\Common\Collections\Collection;

trait TranslatablePropertiesTrait
{
    protected ?string $currentLocale = null;
    protected ?Collection $translations = null;
}

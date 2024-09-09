<?php

namespace Aropixel\AdminBundle\Entity;


use Doctrine\Common\Collections\Collection;

trait TranslatablePropertiesTrait
{

    private ?string $currentLocale = null;
    private ?Collection $translations = null;

}

<?php

namespace Aropixel\AdminBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Translatable\Translatable;

trait TranslatableMethodsTrait
{

    public function getTranslation(string $field): ?string
    {
        foreach ($this->getTranslations() as $translation) {
            if ($field === $translation->getField() && $translation->getLocale() === $this->getCurrentLocale()) {
                return $translation->getContent();
            }
        }

        return $this->$field;
    }

    public function getLocales()
    {
        $languages = [];

        foreach ($this->getTranslations() as $translation) {
            if (!in_array($translation->getLocale(), $languages)) {
                $languages[] = $translation->getLocale();
            }
        }

        return implode(", ", $languages);
    }


    public function removeTranslation($t)
    {
        $this->translations->removeElement($t);
        $t->setObject(null);
    }

    public function getTranslations()
    {
        if ($this->translations === null) {
            $this->translations = new ArrayCollection();
        }
        return $this->translations;
    }

    public function addTranslation($t)
    {
        if (!$this->getTranslations()->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
    }

    // method used when values is set throught a type collection (add new throught the data-prototype)
    public function setTranslations($at)
    {
        foreach ($at as $t) {
            $this->addTranslation($t);
        }
        return $this;
    }

    public function setTranslatableLocale($locale)
    {
        $this->currentLocale = $locale;
    }

    public function getCurrentLocale()
    {
        return $this->currentLocale;
    }

}

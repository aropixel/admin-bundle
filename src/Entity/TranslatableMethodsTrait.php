<?php

namespace Aropixel\AdminBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait TranslatableMethodsTrait
{
    public function getTranslation(string $field): ?string
    {
        foreach ($this->getTranslations() as $translation) {
            if ($field === $translation->getField() && $translation->getLocale() === $this->getCurrentLocale()) {
                return $translation->getContent();
            }
        }

        // return first translation if no translation exists for currentLocale
        foreach ($this->getTranslations() as $translation) {
            if ($field === $translation->getField()) {
                return $translation->getContent();
            }
        }

        return $this->{$field};
    }

    public function getLocales(): string
    {
        $languages = [];

        foreach ($this->getTranslations() as $translation) {
            if (!\in_array($translation->getLocale(), $languages)) {
                $languages[] = $translation->getLocale();
            }
        }

        return implode(', ', $languages);
    }

    public function removeTranslation(object $t): void
    {
        /** @var \Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation $t */
        /** @var \Doctrine\Common\Collections\Collection<int, mixed> $translations */
        $translations = $this->translations;
        $translations->removeElement($t);
        /* @phpstan-ignore-next-line */
        $t->setObject(null);
    }

    /**
     * @return Collection<int, mixed>
     */
    public function getTranslations(): Collection
    {
        if (null === $this->translations) {
            $this->translations = new ArrayCollection();
        }

        return $this->translations;
    }

    public function addTranslation(object $t): void
    {
        if (!$this->getTranslations()->contains($t)) {
            /** @var \Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation $t */
            /** @var \Doctrine\Common\Collections\Collection<int, mixed> $translations */
            $translations = $this->translations;
            $translations[] = $t;
            $t->setObject($this);
        }
    }

    /**
     * @param iterable<mixed> $at
     */
    public function setTranslations(iterable $at): self
    {
        foreach ($at as $t) {
            $this->addTranslation($t);
        }

        return $this;
    }

    public function setTranslatableLocale(string $locale): void
    {
        $this->currentLocale = $locale;
    }

    public function getCurrentLocale(): ?string
    {
        return $this->currentLocale;
    }
}

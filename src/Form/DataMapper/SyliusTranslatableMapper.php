<?php

namespace Aropixel\AdminBundle\Form\DataMapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormInterface;

class SyliusTranslatableMapper implements DataMapperInterface
{
    public function __construct(
        private string $translationClass,
        private string $translationField
    ) {
    }

    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        if (null === $viewData) {
            return;
        }

        if (!$viewData instanceof Collection) {
            throw new UnexpectedTypeException($viewData, Collection::class);
        }

        $forms = iterator_to_array($forms);
        foreach ($viewData as $translation) {
            if (!\is_object($translation)) {
                continue;
            }

            // In Sylius, we check the locale from the translation
            $locale = $translation->getLocale();
            $formName = $this->translationField . ':' . $locale;

            if (isset($forms[$formName])) {
                // Get the content of the field (e.g. getName() for 'name' field)
                $getter = 'get' . ucfirst($this->translationField);
                if (method_exists($translation, $getter)) {
                    $forms[$formName]->setData($translation->$getter());
                }
            }
        }
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $formsArray = iterator_to_array($forms);

        if (null === $viewData || (\is_array($viewData) && 0 === \count($viewData))) {
            $viewData = $this->findExistingCollectionFromSiblings($formsArray);
            if (null === $viewData) {
                $viewData = new ArrayCollection();
            }
        } elseif (\is_array($viewData)) {
            $viewData = new ArrayCollection($viewData);
        }

        if (!$viewData instanceof Collection) {
            throw new UnexpectedTypeException($viewData, Collection::class);
        }

        $parentEntity = $this->getParentEntity($formsArray);

        foreach ($formsArray as $form) {
            $this->processTranslationForm($form, $viewData, $parentEntity);
        }
    }

    private function findExistingCollectionFromSiblings(array $formsArray): ?Collection
    {
        if (empty($formsArray)) {
            return null;
        }

        $currentFieldForm = reset($formsArray);
        $translatableForm = $currentFieldForm->getParent();
        $parentForm = $translatableForm?->getParent();

        if (!$parentForm) {
            return null;
        }

        $currentPath = (string) $translatableForm->getPropertyPath();
        foreach ($parentForm->all() as $sibling) {
            if ($sibling === $translatableForm) {
                continue;
            }

            if ((string) $sibling->getPropertyPath() === $currentPath) {
                $siblingData = $sibling->getData();
                if ($siblingData instanceof Collection) {
                    return $siblingData;
                }
            }
        }

        return null;
    }

    private function getParentEntity(array $formsArray): ?object
    {
        if (empty($formsArray)) {
            return null;
        }

        $firstForm = reset($formsArray);
        return $firstForm->getParent()?->getParent()?->getData();
    }

    private function processTranslationForm(FormInterface $form, Collection $viewData, ?object $parentEntity): void
    {
        $content = $form->getData();
        $formName = $form->getName();

        $parts = explode(':', $formName);
        $locale = end($parts);

        $existingTranslation = null;
        foreach ($viewData as $translation) {
            if (\is_object($translation) && $translation->getLocale() === $locale) {
                $existingTranslation = $translation;
                break;
            }
        }

        if (null === $content || '' === $content) {
            if ($existingTranslation) {
                $viewData->removeElement($existingTranslation);
                if (method_exists($existingTranslation, 'setTranslatable')) {
                    $existingTranslation->setTranslatable(null);
                }
            }
            return;
        }

        $setter = 'set' . ucfirst($this->translationField);

        if ($existingTranslation) {
            if (method_exists($existingTranslation, $setter)) {
                $existingTranslation->$setter($content);
            }
        } else {
            $newTranslation = new $this->translationClass();
            if (method_exists($newTranslation, 'setLocale')) {
                $newTranslation->setLocale($locale);
            }
            if (method_exists($newTranslation, $setter)) {
                $newTranslation->$setter($content);
            }

            if ($parentEntity && method_exists($newTranslation, 'setTranslatable')) {
                $newTranslation->setTranslatable($parentEntity);
            }
            $viewData->add($newTranslation);
        }
    }
}

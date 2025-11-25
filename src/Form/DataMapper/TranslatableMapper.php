<?php

namespace Aropixel\AdminBundle\Form\DataMapper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormInterface;

/**
 * Custom DataMapper for TranslatableType.
 *
 * This mapper handles the conversion between the Translation Collection (Entity side)
 * and the individual locale fields (Form side, e.g., "title:en", "title:fr").
 *
 * It replaces the default Symfony PropertyPathMapper to prevent issues where
 * scalar string values are injected into the Collection during the mapping process.
 */
class TranslatableMapper implements DataMapperInterface
{
    public function __construct(
        private string $translationClass,
        private string $translationField
    ) {
    }

    /**
     * Maps the Entity data (Collection of translations) to the Form fields.
     *
     * @param Collection|null $viewData The collection of Translation entities.
     * @param \Traversable $forms The list of form fields (e.g., "title:en", "title:fr").
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        // If the entity is new, viewData might be null.
        if (null === $viewData) {
            return;
        }

        // Strict type check to ensure we are working with a Doctrine Collection.
        if (!$viewData instanceof Collection) {
            throw new UnexpectedTypeException($viewData, Collection::class);
        }

        $forms = iterator_to_array($forms);

        foreach ($viewData as $translation) {
            // Safety check against corrupted data.
            if (!is_object($translation)) {
                continue;
            }

            // Construct the form field name based on the locale (e.g., "question:fr").
            $locale = $translation->getLocale();
            $formName = $this->translationField . ':' . $locale;

            // If the field exists in the form, set its data.
            if (isset($forms[$formName])) {
                $forms[$formName]->setData($translation->getContent());
            }
        }
    }

    /**
     * Maps the submitted Form data back to the Entity (Collection).
     *
     * This method handles:
     * 1. Initialization of the Collection if null.
     * 2. Creation of new Translation entities.
     * 3. Update of existing Translation entities.
     * 4. Removal of empty translations.
     * 5. *Crucial*: Linking the Translation to its parent Entity (owning side) to ensure cascade persistence.
     *
     * @param \Traversable $forms The submitted form fields.
     * @param Collection|null $viewData The original collection from the entity (passed by reference).
     */
    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        // Ensure $viewData is a valid Collection.
        // Symfony might pass null (new entity) or an array (if transformed upstream).
        if (null === $viewData) {
            $viewData = new ArrayCollection();
        } elseif (\is_array($viewData)) {
            $viewData = new ArrayCollection($viewData);
        }

        if (!$viewData instanceof Collection) {
            throw new UnexpectedTypeException($viewData, Collection::class);
        }

        $forms = iterator_to_array($forms);

        // Retrieve the parent entity (e.g., AccordionItem) to correctly link the new translation.
        // We access it via the parent form of the first child.
        $parentEntity = null;
        if (count($forms) > 0) {
            $firstForm = reset($forms);
            // Child (field:locale) -> TranslatableType -> ParentType (e.g. AccordionItemType) -> getData()
            $parentEntity = $firstForm->getParent()?->getParent()?->getData();
        }

        foreach ($forms as $form) {
            /** @var FormInterface $form */
            $content = $form->getData();
            $formName = $form->getName();

            // Extract locale from the field name "field:locale".
            $parts = explode(':', $formName);
            $locale = end($parts);

            // 1. Search for an existing translation in the collection.
            $existingTranslation = null;
            foreach ($viewData as $translation) {
                if (is_object($translation) &&
                    $translation->getLocale() === $locale &&
                    $translation->getField() === $this->translationField
                ) {
                    $existingTranslation = $translation;
                    break;
                }
            }

            // 2. Handle removal (if content is empty).
            if ($content === null || $content === '') {
                if ($existingTranslation) {
                    $viewData->removeElement($existingTranslation);
                    // Break the association for Doctrine.
                    if (method_exists($existingTranslation, 'setObject')) {
                        $existingTranslation->setObject(null);
                    }
                }
                continue;
            }

            // 3. Update or Create translation.
            if ($existingTranslation) {
                $existingTranslation->setContent($content);
            } else {
                // Create new translation instance.
                $newTranslation = new $this->translationClass($locale, $this->translationField, $content);

                // CRITICAL: Explicitly set the owning side of the relation.
                // This ensures that Doctrine persists the translation even if the parent entity is new (no ID yet).
                if ($parentEntity && method_exists($newTranslation, 'setObject')) {
                    $newTranslation->setObject($parentEntity);
                }

                $viewData->add($newTranslation);
            }
        }
    }
}

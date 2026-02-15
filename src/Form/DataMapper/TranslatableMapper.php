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
     * @param Collection<int, object>|array<int, object>|null $viewData The collection of Translation entities.
     * @param \Traversable<string, FormInterface> $forms The list of form fields (e.g., "title:en", "title:fr").
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
     * @param \Traversable<string, FormInterface> $forms The submitted form fields.
     * @param Collection<int, object>|array<int, object>|null $viewData The original collection from the entity (passed by reference).
     */
    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $formsArray = iterator_to_array($forms);

        // 1. Initialize or recover the collection
        if (null === $viewData || (\is_array($viewData) && \count($viewData) === 0)) {
            // Try to recover an existing collection from sibling forms (e.g., 'question' might have already initialized the collection for 'answer')
            $viewData = $this->findExistingCollectionFromSiblings($formsArray);

            // If still null, initialize a new collection
            if (null === $viewData) {
                $viewData = new ArrayCollection();
            }
        } elseif (\is_array($viewData)) {
            $viewData = new ArrayCollection($viewData);
        }

        if (!$viewData instanceof Collection) {
            throw new UnexpectedTypeException($viewData, Collection::class);
        }

        // 2. Retrieve the parent entity to correctly link the translation
        $parentEntity = $this->getParentEntity($formsArray);

        // 3. Process each form field (locale)
        foreach ($formsArray as $form) {
            $this->processTranslationForm($form, $viewData, $parentEntity);
        }
    }

    /**
     * Tries to find an existing Collection instance in sibling forms.
     * This handles cases where multiple TranslatableFields (e.g. "question", "answer")
     * are mapped to the same property "translations" on a new entity.
     *
     * @param array<string, FormInterface> $formsArray The current form fields
     * @return Collection<int, object>|null
     */
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
            // Ignore self
            if ($sibling === $translatableForm) {
                continue;
            }

            // If a sibling points to the same property (e.g. "translations")
            // and already holds data (Collection), reuse that instance.
            if ((string) $sibling->getPropertyPath() === $currentPath) {
                $siblingData = $sibling->getData();
                if ($siblingData instanceof Collection) {
                    return $siblingData;
                }
            }
        }

        return null;
    }

    /**
     * Helper to retrieve the parent entity (e.g. AccordionItem) from the form structure.
     *
     * @param array<string, FormInterface> $formsArray
     * @return object|null
     */
    private function getParentEntity(array $formsArray): ?object
    {
        if (empty($formsArray)) {
            return null;
        }

        $firstForm = reset($formsArray);
        // Child (field:locale) -> TranslatableType -> ParentType (e.g. AccordionItemType) -> getData()
        return $firstForm->getParent()?->getParent()?->getData();
    }

    /**
     * Process a single form field submission (one locale).
     * Updates, creates, or removes the translation from the collection.
     *
     * @param FormInterface $form The specific form field (e.g. "title:en")
     * @param Collection<int, object> $viewData The collection of translations
     * @param object|null $parentEntity The owner entity (for linking new translations)
     */
    private function processTranslationForm(FormInterface $form, Collection $viewData, ?object $parentEntity): void
    {
        $content = $form->getData();
        $formName = $form->getName();

        // Extract locale from the field name "field:locale".
        $parts = explode(':', $formName);
        $locale = end($parts);

        // Find existing translation in the collection
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

        // Case: Content is empty -> Remove translation
        if ($content === null || $content === '') {
            if ($existingTranslation) {
                $viewData->removeElement($existingTranslation);
                // Break the association for Doctrine to ensure orphan removal/cleanup
                if (method_exists($existingTranslation, 'setObject')) {
                    $existingTranslation->setObject(null);
                }
            }
            return;
        }

        // Case: Content exists -> Update or Create
        if ($existingTranslation) {
            $existingTranslation->setContent($content);
        } else {
            // Create new translation instance
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

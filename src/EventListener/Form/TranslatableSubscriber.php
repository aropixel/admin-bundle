<?php

namespace Aropixel\AdminBundle\EventListener\Form;

use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Event Subscriber for TranslatableType.
 *
 * Responsibilities:
 * 1. PRE_SET_DATA: Dynamically adds form fields for each locale (e.g. "title:en", "title:fr").
 * 2. SUBMIT: Handles manual validation for required locales.
 *
 * Note: Persistence logic (mapping data to entity) is handled by TranslatableMapper, not here.
 */
class TranslatableSubscriber implements EventSubscriberInterface
{
    /**
     * @param array<mixed>|null $options
     */
    public function __construct(
        private readonly FormFactoryInterface $factory,
        private readonly ValidatorInterface $validator,
        private readonly ?array $options = null
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::SUBMIT => 'submit',
        ];
    }

    /**
     * Generates form fields for each locale.
     *
     * If data is null (creation), it creates empty fields.
     * If data exists (edit), it pre-fills fields using bindTranslations().
     */
    public function preSetData(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        // Case: Creation (no data yet). Create empty fields for all locales.
        if (null === $data) {
            foreach ($this->options['locales'] as $locale) {
                $class_name = $this->options['personal_translation'];
                /** @var AbstractPersonalTranslation $translation */
                $translation = new $class_name($locale, $this->options['field'], null);
                $object = $translation->getContent();

                $form->add($this->factory->createNamed(
                    $this->options['field'] . ':' . $locale,
                    $this->options['widget'],
                    $object,
                    [
                        'auto_initialize' => false,
                        'label' => $locale,
                        'required' => \in_array($locale, $this->options['required_locale']),
                        'property_path' => null, // Mapping is handled manually by TranslatableMapper
                        'attr' => $this->options['attr'],
                    ]
                ));
            }
            return;
        }

        // Case: Edition. Map existing translations to fields.
        foreach ($this->bindTranslations($data) as $binded) {
            $translation = $binded['translation'];
            $required = $form->getConfig()->getRequired();

            $form->add($this->factory->createNamed(
                $binded['fieldName'],
                $this->options['widget'],
                $translation->getContent(),
                [
                    'auto_initialize' => false,
                    'label' => $binded['locale'],
                    'required' => $required && \in_array($binded['locale'], $this->options['required_locale']),
                    'property_path' => null,
                    'attr' => $this->options['attr'],
                ]
            ));
        }
    }

    /**
     * Validates the submitted data.
     *
     * Checks if required locales have content.
     * Validates the translation entity itself using the Validator service.
     */
    public function submit(FormEvent $event): void
    {
        $form = $event->getForm();

        foreach ($this->getFieldNames() as $locale => $field_name) {
            $content = $form->get($field_name)->getData();
            $required = $form->get($field_name)->getConfig()->getRequired();

            if ($required && null === $content && \in_array($locale, $this->options['required_locale'])) {
                $form->addError($this->getCannotBeBlankException($this->options['field'], $locale));
            } else {
                // Validate the translation object (e.g. constraints on content)
                $errors = $this->validator->validate(
                    $this->createPersonalTranslation($locale, $field_name, $content, $form->getParent()->getData())
                );
                foreach ($errors as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }
    }

    /**
     * Helper to extract relevant translations from the collection for the current field.
     *
     * @param iterable<int, object> $data Collection of translations (from entity)
     * @return array<mixed>
     */
    private function bindTranslations(iterable $data): array
    {
        $collection = [];
        $available_translations = [];
        foreach ($data as $translation) {
            if (\is_object($translation)
                && mb_strtolower($translation->getField()) == mb_strtolower((string) $this->options['field'])
            ) {
                $available_translations[mb_strtolower($translation->getLocale())] = $translation;
            }
        }

        foreach ($this->getFieldNames() as $locale => $field_name) {
            if (isset($available_translations[mb_strtolower($locale)])) {
                $translation = $available_translations[mb_strtolower($locale)];
            } else {
                $translation = $this->createPersonalTranslation($locale, $this->options['field'], null, null);
            }

            $collection[] = [
                'locale' => $locale,
                'fieldName' => $field_name,
                'translation' => $translation,
            ];
        }

        return $collection;
    }

    /**
     * Generates field names like "title:en", "title:fr".
     * @return array<string,string>
     */
    private function getFieldNames(): array
    {
        $collection = [];
        foreach ($this->options['locales'] as $locale) {
            $collection[$locale] = $this->options['field'] . ':' . $locale;
        }
        return $collection;
    }

    private function createPersonalTranslation(string $locale, string $field, mixed $content, mixed $foreignKey): AbstractPersonalTranslation
    {
        $class_name = $this->options['personal_translation'];
        return new $class_name($locale, $field, $content, $foreignKey);
    }

    public function getCannotBeBlankException(string $field, string $locale): FormError
    {
        return new FormError(sprintf('Field "%s" for locale "%s" cannot be blank', $field, $locale));
    }
}

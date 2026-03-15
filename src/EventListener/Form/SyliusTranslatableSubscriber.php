<?php

namespace Aropixel\AdminBundle\EventListener\Form;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SyliusTranslatableSubscriber implements EventSubscriberInterface
{
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

    public function preSetData(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            foreach ($this->options['locales'] as $locale) {
                $form->add($this->factory->createNamed(
                    $this->options['field'] . ':' . $locale,
                    $this->options['widget'],
                    null,
                    [
                        'auto_initialize' => false,
                        'label' => $locale,
                        'required' => \in_array($locale, $this->options['required_locale']),
                        'property_path' => null,
                        'attr' => $this->options['attr'],
                    ]
                ));
            }
            return;
        }

        foreach ($this->bindTranslations($data) as $binded) {
            $required = $form->getConfig()->getRequired();
            $form->add($this->factory->createNamed(
                $binded['fieldName'],
                $this->options['widget'],
                $binded['content'],
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

    public function submit(FormEvent $event): void
    {
        $form = $event->getForm();
        foreach ($this->getFieldNames() as $locale => $field_name) {
            $content = $form->get($field_name)->getData();
            $required = $form->get($field_name)->getConfig()->getRequired();
            if ($required && null === $content && \in_array($locale, $this->options['required_locale'])) {
                $form->addError($this->getCannotBeBlankException($this->options['field'], $locale));
            } else {
                $translation = new $this->options['personal_translation']();
                if (method_exists($translation, 'setLocale')) {
                    $translation->setLocale($locale);
                }
                $setter = 'set' . ucfirst($this->options['field']);
                if (method_exists($translation, $setter)) {
                    $translation->$setter($content);
                }

                $errors = $this->validator->validate($translation);
                foreach ($errors as $error) {
                    $form->addError(new FormError($error->getMessage()));
                }
            }
        }
    }

    private function bindTranslations(iterable $data): array
    {
        $collection = [];
        $available_translations = [];
        foreach ($data as $translation) {
            if (\is_object($translation)) {
                $available_translations[mb_strtolower($translation->getLocale())] = $translation;
            }
        }

        foreach ($this->getFieldNames() as $locale => $field_name) {
            $content = null;
            if (isset($available_translations[mb_strtolower($locale)])) {
                $translation = $available_translations[mb_strtolower($locale)];
                $getter = 'get' . ucfirst($this->options['field']);
                if (method_exists($translation, $getter)) {
                    $content = $translation->$getter();
                }
            }

            $collection[] = [
                'locale' => $locale,
                'fieldName' => $field_name,
                'content' => $content,
            ];
        }
        return $collection;
    }

    private function getFieldNames(): array
    {
        $collection = [];
        foreach ($this->options['locales'] as $locale) {
            $collection[$locale] = $this->options['field'] . ':' . $locale;
        }
        return $collection;
    }

    public function getCannotBeBlankException(string $field, string $locale): FormError
    {
        return new FormError(sprintf('Field "%s" for locale "%s" cannot be blank', $field, $locale));
    }
}

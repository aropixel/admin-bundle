<?php

namespace Aropixel\AdminBundle\Form\Type;

use Aropixel\AdminBundle\Form\DataMapper\TranslatableMapper;
use Aropixel\AdminBundle\Form\Subscriber\Translatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Form Type for handling Gedmo Personal Translations.
 *
 * This type generates one input field per configured locale.
 * It uses a custom DataMapper to handle the persistence logic within Doctrine Collections.
 */
class TranslatableType extends AbstractType
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ValidatorInterface $validator,
        protected readonly ParameterBagInterface $parameterBag,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!class_exists($options['personal_translation'])) {
            throw $this->getNoPersonalTranslationException($options['personal_translation']);
        }

        $options['field'] = $options['field'] ?: $builder->getName();
        $isPageFieldTranslation = $options['personal_translation'] === 'Aropixel\PageBundle\Entity\FieldTranslation';

        if (!$isPageFieldTranslation) {
            $options['empty_data'] = fn(FormInterface $form) => new ArrayCollection();

            // Use custom DataMapper to bypass Symfony's default mapping.
            // This allows precise control over how translations are added to the collection
            // and linked to the parent entity.
            $builder->setDataMapper(new TranslatableMapper(
                $options['personal_translation'],
                $options['field']
            ));
        }

        // Register subscriber to dynamically add fields for each locale.
        $builder->addEventSubscriber(
            new Translatable($builder->getFormFactory(), $this->validator, $options)
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults($this->getDefaultOptions());
    }

    /**
     * @param array<mixed> $options
     * @return array<mixed>
     */
    public function getDefaultOptions(array $options = []): array
    {
        $locale = $this->parameterBag->get('kernel.default_locale');
        $locales = $this->parameterBag->get('aropixel_admin.locales');

        $options['remove_empty'] = true; // Personal Translations without content are removed
        $options['csrf_protection'] = false;
        $options['personal_translation'] = false; // Personal Translation class (e.g., ProductTranslation::class)
        $options['locales'] = $locales; // The locales available for editing
        $options['required_locale'] = [$locale]; // Locales that cannot be blank
        $options['field'] = false; // The entity field being translated (e.g., "title")
        $options['widget'] = TextType::class; // Underlying widget type (TextType, TextareaType, etc.)
        $options['entity_manager_removal'] = true; // Auto removes the Personal Translation thru entity manager
        $options['attr'] = [];

        return $options;
    }

    public function getNoPersonalTranslationException(string $translation): \InvalidArgumentException
    {
        return new \InvalidArgumentException(sprintf('Unable to find personal translation class: "%s"', $translation));
    }

    public function getName(): string
    {
        return 'translatable';
    }
}

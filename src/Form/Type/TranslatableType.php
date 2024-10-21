<?php

namespace Aropixel\AdminBundle\Form\Type;

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


class TranslatableType extends AbstractType
{

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ValidatorInterface $validator,
        protected readonly ParameterBagInterface $parameterBag,
    ){}


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!class_exists($options['personal_translation'])) {
            throw $this->getNoPersonalTranslationException($options['personal_translation']);
        }

        $options['field'] = $options['field'] ?: $builder->getName();
        $options['empty_data'] = function (FormInterface $form) {
            return new ArrayCollection();
        };
        $builder->addEventSubscriber(
            new Translatable($builder->getFormFactory(), $this->em, $this->validator, $options)
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults($this->getDefaultOptions());
    }

    public function getDefaultOptions(array $options = []) : array
    {
        $locale = $this->parameterBag->get('kernel.default_locale');
        $locales = $this->parameterBag->get('aropixel_admin.locales');

        $options['remove_empty'] = true; // Personal Translations without content are removed
        $options['csrf_protection'] = false;
        $options['personal_translation'] = false; // Personal Translation class
        $options['locales'] = $locales; // the locales you wish to edit
        $options['required_locale'] = [$locale]; // the required locales cannot be blank
        $options['field'] = false; // the field that you wish to translate
        $options['widget'] = TextType::class; // change this to another widget like 'texarea' if needed
        $options['entity_manager_removal'] = true; // auto removes the Personal Translation thru entity manager
        $options['attr'] = [];

        return $options;
    }

    public function getNoPersonalTranslationException(string $translation) : \InvalidArgumentException
    {
        return new \InvalidArgumentException(sprintf('Unable to find personal translation class: "%s"', $translation));
    }

    public function getName() : string
    {
        return 'translatable';
    }

}

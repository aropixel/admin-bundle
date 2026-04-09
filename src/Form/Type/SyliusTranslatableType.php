<?php

namespace Aropixel\AdminBundle\Form\Type;

use Aropixel\AdminBundle\EventListener\Form\SyliusTranslatableSubscriber;
use Aropixel\AdminBundle\Form\DataMapper\SyliusTranslatableMapper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SyliusTranslatableType extends AbstractType
{
    public function __construct(
        protected readonly ValidatorInterface $validator,
        protected readonly ParameterBagInterface $parameterBag,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $options['field'] = $options['field'] ?: $builder->getName();
        $options['empty_data'] = fn (FormInterface $form) => new ArrayCollection();

        $builder->setDataMapper(new SyliusTranslatableMapper(
            $options['personal_translation'],
            $options['field']
        ));

        $builder->addEventSubscriber(
            new SyliusTranslatableSubscriber($builder->getFormFactory(), $this->validator, $options)
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults($this->getDefaultOptions());
    }

    public function getDefaultOptions(): array
    {
        $locale = $this->parameterBag->get('kernel.default_locale');
        $locales = $this->parameterBag->get('aropixel_admin.locales');

        return [
            'remove_empty' => true,
            'csrf_protection' => false,
            'personal_translation' => false,
            'locales' => $locales,
            'required_locale' => [$locale],
            'field' => false,
            'widget' => TextType::class,
            'attr' => [],
        ];
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_translatable';
    }
}

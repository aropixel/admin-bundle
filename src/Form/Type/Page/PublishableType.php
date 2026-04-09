<?php

namespace Aropixel\AdminBundle\Form\Type\Page;

use Aropixel\AdminBundle\Form\Type\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType for publishable entities (status and date ranges).
 */
class PublishableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, ['choices' => ['text.yes' => 'online', 'text.no' => 'offline'], 'empty_data' => 'offline', 'expanded' => true])
        ;

        if (false !== $options['publishAt']) {
            $builder
                ->add('publishAt', DateTimeType::class, ['label' => 'form.label.publish_at', 'required' => false, 'years' => range(date('Y') - 50, date('Y') + 50)])
            ;
        }

        if (false !== $options['publishUntil']) {
            $builder
                ->add('publishUntil', DateTimeType::class, ['label' => 'form.label.publish_until', 'required' => false, 'years' => range(date('Y') - 50, date('Y') + 50)])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['publishAt' => false, 'publishUntil' => false])
        ;
    }
}

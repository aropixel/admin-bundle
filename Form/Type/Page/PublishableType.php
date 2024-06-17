<?php

namespace Aropixel\AdminBundle\Form\Type\Page;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublishableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', ChoiceType::class, ['choices' => ['Oui' => 'online', 'Non' => 'offline'], 'empty_data' => 'Non', 'expanded' => true])
        ;

        if (false !== $options['publishAt']) {
            $builder
                ->add('publishAt', DateTimeType::class, ['label' => 'Publié le', 'required' => false, 'date_widget' => 'single_text', 'time_widget' => 'single_text', 'date_format' => 'yyyy-MM-dd', 'years' => range(date('Y') - 50, date('Y') + 50)])
            ;
        }

        if (false !== $options['publishUntil']) {
            $builder
                ->add('publishUntil', DateTimeType::class, ['label' => "Jusqu'au", 'required' => false, 'date_widget' => 'single_text', 'time_widget' => 'single_text', 'date_format' => 'yyyy-MM-dd', 'years' => range(date('Y') - 50, date('Y') + 50)])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(['publishAt' => false, 'publishUntil' => false])
        ;
    }
}

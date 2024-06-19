<?php

namespace Aropixel\AdminBundle\Form\Type\Page;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (false !== $options['createdAt']) {
            $builder
                ->add('createdAt', DateTimeType::class, ['label' => 'Créé le', 'required' => false, 'date_widget' => 'single_text', 'time_widget' => 'single_text', 'date_format' => 'yyyy-MM-dd'])
            ;
        }

        if (false !== $options['updatedAt']) {
            $builder
                ->add('updatedAt', DateTimeType::class, ['required' => false, 'date_widget' => 'single_text', 'time_widget' => 'single_text', 'date_format' => 'yyyy-MM-dd'])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(['createdAt' => true, 'updatedAt' => false])
        ;
    }
}

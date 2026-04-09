<?php

namespace Aropixel\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as SymfonyDateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'date_widget' => 'single_text',
            'time_widget' => 'single_text',
            'date_format' => 'yyyy-MM-dd',
        ]);
    }

    public function getParent(): string
    {
        return SymfonyDateTimeType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_datetime';
    }
}

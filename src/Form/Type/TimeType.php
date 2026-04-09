<?php

namespace Aropixel\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimeType as SymfonyTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
        ]);
    }

    public function getParent(): string
    {
        return SymfonyTimeType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_time';
    }
}

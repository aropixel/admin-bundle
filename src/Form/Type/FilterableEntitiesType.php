<?php

namespace Aropixel\AdminBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterableEntitiesType extends FilterableEntityType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('multiple', true);
    }
}

<?php

namespace Aropixel\AdminBundle\Form\Type\Image\Single;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AttachImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function getName()
    {
        return 'aropixel_image';
    }
}

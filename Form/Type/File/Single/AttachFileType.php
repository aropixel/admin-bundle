<?php

namespace Aropixel\AdminBundle\Form\Type\File\Single;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AttachFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function getName()
    {
        return 'aropixel_file';
    }
}

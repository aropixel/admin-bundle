<?php

namespace Aropixel\AdminBundle\Form\Type\Image;

use Aropixel\AdminBundle\Entity\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType used for AJAX image uploads via Plupload.
 */
class PluploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('file', FileType::class)
            ->add('category')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Image::class, 'csrf_protection' => false]);
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_plupload_image';
    }
}

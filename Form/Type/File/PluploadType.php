<?php

namespace Aropixel\AdminBundle\Form\Type\File;

use Aropixel\AdminBundle\Entity\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PluploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('file', FileType::class)
            ->add('category')
            ->add('public')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => File::class, 'csrf_protection' => false]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'plupload_file';
    }
}

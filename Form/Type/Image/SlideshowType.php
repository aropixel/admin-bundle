<?php

namespace Aropixel\AdminBundle\Form\Type\Image;

use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SlideshowType extends AbstractType
{
    public function getParent()
    {
        return CollectionType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(['entry_type' => ImageType::class, 'entry_options' => [
                'data_type' => 'file_name',
            ], 'allow_add' => true, 'allow_delete' => true, 'grid' => 'col-md-6', 'image_label' => 'Image', 'image_value' => null, 'image_crops' => []])
        ;
        $resolver->setRequired([
            'image_class',      // The class of the entity that store the image
            'image_library',    // The class of the entity for which you want to display the library
        ]);

        $resolver->setNormalizer('entry_options', static function (Options $options, $entryOptions) {
            $entryOptions['label'] = $options['image_label'];
            $entryOptions['crops'] = $options['image_crops'];
            $entryOptions['library'] = $options['image_library'];
            $entryOptions['filename_class'] = $options['image_class'];
            $entryOptions['filename_value'] = $options['image_value'];
            $entryOptions['row_attr'] = ['class' => $options['grid']];

            return $entryOptions;
        });
    }

    public function getBlockPrefix()
    {
        return 'aropixel_admin_slideshow';
    }
}

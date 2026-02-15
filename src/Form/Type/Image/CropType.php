<?php

namespace Aropixel\AdminBundle\Form\Type\Image;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType representing a single crop configuration (filter and coordinates).
 *
 * Twig block: aropixel_admin_crop_row
 *
 * Options:
 * - filter: The LiipImagine filter name.
 * - crop: The crop coordinates (json string).
 */
class CropType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filter', HiddenType::class)
            ->add('crop', HiddenType::class)
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // If the crops to display was specified in the form configuration
        if (\array_key_exists('file_name', $options) && $options['file_name']) {
            $view->vars['file_name'] = $options['file_name'];
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null, 'image_class' => null, 'file_name' => null, 'crops' => null]);
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_crop';
    }
}

<?php

namespace Aropixel\AdminBundle\Form\Type\Image\Single;

use Aropixel\AdminBundle\Form\Type\Image\CropType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType representing a collection of crops for a single image.
 *
 * Twig block: aropixel_admin_crops_row
 */
class CropsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['entry_type' => CropType::class, 'allow_add' => true, 'by_reference' => false, 'image_class' => '', 'image_value' => '', 'crops_value' => '', 'crops' => null, 'suffix' => '']);
    }

    /**
     * Pass the image URL to the view.
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var Form $imageForm */
        $imageForm = $form->getParent();
        $imageData = $imageForm->getData();

        $view->vars['image'] = $imageData;
        $view->vars['suffix'] = $options['suffix'];
        $view->vars['optional_available_crop_list'] = \array_key_exists('crops', $options) ? $options['crops'] : null;
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_crops';
    }
}

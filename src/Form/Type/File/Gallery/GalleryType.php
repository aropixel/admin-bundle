<?php

namespace Aropixel\AdminBundle\Form\Type\File\Gallery;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType for a file gallery (collection of files).
 *
 * Twig block: aropixel_admin_gallery_files_row
 */
class GalleryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => GalleryFileType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'multiple' => true,
            'accept' => null,
        ]);
    }

    /**
     * Pass the image URL to the view.
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $entryOptions = $form->getConfig()->getOption('entry_options');
        $shortClassItems = explode('\\', (string) $entryOptions['data_class']);
        $shortClass = array_pop($shortClassItems);

        // set an "image_url" variable that will be available when rendering this field
        $view->vars['imageLongClass'] = $entryOptions['data_class'];
        $view->vars['imageShortClass'] = $shortClass;
        $view->vars['multiple'] = $options['multiple'];
        $view->vars['accept'] = $options['accept'];
    }

    public function getParent(): ?string
    {
        return CollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_gallery_files';
    }
}

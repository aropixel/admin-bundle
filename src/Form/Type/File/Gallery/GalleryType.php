<?php

namespace Aropixel\AdminBundle\Form\Type\File\Gallery;

use Aropixel\AdminBundle\Form\Type\File\Gallery\GalleryFileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GalleryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['entry_type' => GalleryFileType::class, 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false, 'multiple' => true]);
    }

    /**
     * Pass the image URL to the view.
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $data = $form->getParent()->getData();

        $entryOptions = $form->getConfig()->getOption('entry_options');
        $shortClassItems = explode('\\', (string) $entryOptions['data_class']);
        $shortClass = array_pop($shortClassItems);

        // set an "image_url" variable that will be available when rendering this field
        $view->vars['imageLongClass'] = $entryOptions['data_class'];
        $view->vars['imageShortClass'] = $shortClass;
        $view->vars['multiple'] = $options['multiple'];
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    public function getBlockPrefix()
    {
        return 'gallery_files';
    }
}

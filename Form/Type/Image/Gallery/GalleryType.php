<?php
/**
 * Créé par Aropixel @2017.
 * Par: Joël Gomez Caballe
 * Date: 06/03/2017 à 15:26
 */

namespace Aropixel\AdminBundle\Form\Type\Image\Gallery;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GalleryType extends AbstractType
{


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'entry_type' => GalleryImageType::class,
            'allow_add'    => true,
            'allow_delete' => true,
            'by_reference' => false,
        ));
    }

    /**
     * Pass the image URL to the view
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        //
        $data = $form->getParent()->getData();

        //
        $entryOptions = $form->getConfig()->getOption('entry_options');
        $shortClassItems = explode('\\', $entryOptions['data_class']);
        $shortClass = array_pop($shortClassItems);

        // set an "image_url" variable that will be available when rendering this field
        $view->vars['imageLongClass'] = $entryOptions['data_class'];
        $view->vars['imageShortClass'] = $shortClass;

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


    public function getName()
    {
        return 'aropixel_gallery';
    }

}

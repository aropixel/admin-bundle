<?php
/**
 * Créé par Aropixel @2017.
 * Par: Joël Gomez Caballe
 * Date: 03/03/2017 à 12:08
 */

namespace Aropixel\AdminBundle\Form\Type\Image\Gallery;

use Aropixel\AdminBundle\Form\Type\Image\CropType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;


class GalleryCropsType extends AbstractType
{


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'entry_type' => CropType::class,
            'allow_add'    => true,
            'allow_delete'    => true,
            'by_reference' => false,
            'image_class' => null,
            'image_value' => null,
            'image_crops' => null,
            'crops' => [],
            'suffix' => null,
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
        $shortClassItems = explode('\\', $options['image_class']);
        $shortClass = array_pop($shortClassItems);

        // set an "image_url" variable that will be available when rendering this field
        $view->vars['suffix'] = $options['suffix'];
        $view->vars['attachedImage'] = $data;
        $view->vars['imageLongClass'] = $options['image_class'];
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


    public function getBlockPrefix()
    {
        return 'gallery_aropixel_admin_crops';
    }

}

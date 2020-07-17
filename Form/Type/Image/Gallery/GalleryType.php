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
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GalleryType extends AbstractType
{
    //
    private $cropSuffix;


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(

            // Common propreties
            'entry_type' => GalleryImageType::class,
            'entry_options' => [],
            'allow_add'    => true,
            'allow_delete' => true,
            'by_reference' => false,
            'grid' => 'col-md-6 col-lg-6',

            // Filename mode
            'image_library' => null,                // Override the image_class as a category of images in the library
            'image_class' => null,                  // The class name of the image entity (can be an AttachImage entity or a custom entity)
            'image_value' => null,                  // The property where the file_name is stored in a custom entity
            'image_crops' => 'crops',               // The property where the crops info are stored in a custom entity
            'image_attributes' => 'attributes',     // The property where the attributes of the image (title, alt, css class) are stored in a custom entity
            'crops' => [],                          // The crops defined directly in the form configuration
        ));

        $resolver->setNormalizer('entry_options', static function (Options $options, $entryOptions) {

            // The class name of the image entity (can be an AttachImage entity or a custom entity)
            if ($options['image_class']) {
                $entryOptions['data_class'] = $options['image_class'];
            }

            // The property where the file_name is stored in a custom entity
            if ($options['image_value']) {
                $entryOptions['data_value'] = $options['image_value'];
            }

            // The property where the crops info are stored in a custom entity
            if ($options['image_crops']) {
                $entryOptions['data_crops'] = $options['image_crops'];
            }

            // The property where the crops info are stored in a custom entity
            if ($options['image_attributes']) {
                $entryOptions['data_attributes'] = $options['image_attributes'];
            }

            //
            $entryOptions['crops'] = $options['crops'];
            $entryOptions['grid'] = $options['grid'];

            return $entryOptions;
        });

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
        $this->cropSuffix = uniqid();
        $view->vars['crop_suffix'] = $this->cropSuffix;

        //
        $shortClassItems = explode('\\', $options['image_class']);
        $shortClass = array_pop($shortClassItems);

        // set an "image_url" variable that will be available when rendering this field
        $view->vars['image_class'] = $options['image_class'];
        $view->vars['image_class_short'] = $shortClass;
        $view->vars['image_library'] = $options['image_library'] ?: ($options['image_class'] ?: '');

        $view->vars['image_value'] = $options['image_value'];
        $view->vars['image_crops'] = $options['image_crops'];
        $view->vars['crops'] = $options['crops'];

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
        return 'aropixel_gallery';
    }

}

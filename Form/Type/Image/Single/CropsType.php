<?php
/**
 * Créé par Aropixel @2017.
 * Par: Joël Gomez Caballe
 * Date: 03/03/2017 à 12:08
 */

namespace Aropixel\AdminBundle\Form\Type\Image\Single;

use Aropixel\AdminBundle\Form\Type\Image\CropType;
use Aropixel\AdminBundle\Form\Type\Image\InstanceToData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CropsType extends AbstractType
{

    /**
     * @var InstanceToData
     */
    private $instanceToData;


    /**
     * CropsType constructor.
     * @param InstanceToData $instanceToData
     */
    public function __construct(InstanceToData $instanceToData)
    {
        $this->instanceToData = $instanceToData;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'entry_type' => CropType::class,
            'allow_add'    => true,
            'by_reference' => false,
            'image_class' => '',
            'image_value' => '',
            'crops_value' => '',
            'crops' => '',
            'suffix' => '',
        ));
//
//        $resolver->setNormalizer('entry_options', static function (Options $options, $entryOptions) {
//
//            $entryOptions['data_class'] = $options['data_class'];
//            $entryOptions['image_class'] = $options['image_class'];
//            return $entryOptions;
//
//        });
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
        /** @var Form $imageForm */
        $imageForm = $form->getParent();
        $imageData = $imageForm->getData();


        //
        $view->vars['image'] = $imageData;
        $view->vars['suffix'] = $options['suffix'];

        //
        if (array_key_exists('crops', $options)) {
            $view->vars['crops'] = $options['crops'];
        }

        //
        if (array_key_exists('image_value', $options) && strlen($options['image_value'])) {

            $this->instanceToData->setFilenameValue($options['image_value']);
            $this->instanceToData->setCropsValue($options['crops_value']);
            $view->vars['file_name'] = $this->instanceToData->getFileName($imageData);
            $view->vars['image_path'] = $view->vars['file_name'];
            $view->vars['image_value'] = $options['image_value'];

        }

        $entryOptions = $form->getConfig()->getOption('entry_options');
        if (array_key_exists('image_class', $entryOptions) && $entryOptions['image_class']) {

//            $shortClassItems = explode('\\', $entryOptions['image_class']);
//            $shortClass = array_pop($shortClassItems);

            // set an "image_url" variable that will be available when rendering this field
            $view->vars['image_class'] = $entryOptions['image_class'];
//            $view->vars['imageShortClass'] = $shortClass;

        }

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
        return 'aropixel_admin_crops';
    }

}

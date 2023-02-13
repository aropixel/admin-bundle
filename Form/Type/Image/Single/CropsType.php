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
            'crops' => null,
            'suffix' => '',
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
        /** @var Form $imageForm */
        $imageForm = $form->getParent();
        $imageData = $imageForm->getData();


        $view->vars['image'] = $imageData;
        $view->vars['suffix'] = $options['suffix'];
        $view->vars['optional_available_crop_list'] = array_key_exists('crops', $options) ? $options['crops'] : null;
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

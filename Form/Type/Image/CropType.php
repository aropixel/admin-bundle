<?php
/**
 * Créé par Aropixel @2017.
 * Par: Joël Gomez Caballe
 * Date: 01/03/2017 à 00:08
 */

namespace Aropixel\AdminBundle\Form\Type\Image;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;



class CropType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('filter', HiddenType::class)
            ->add('crop', HiddenType::class)
        ;
    }


    public function buildView(FormView $view, FormInterface $form, array $options)
    {

        // If the crops to display was specified in the form configuration
        if (array_key_exists('file_name', $options) && $options['file_name']) {
            $view->vars['file_name'] = $options['file_name'];
        }
//        parent::buildView($view, $form, $options);

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'image_class' => null,
            'file_name' => null,
            'crops' => null,
        ));
    }



    public function getName()
    {
        return 'aropixel_crop';
    }
}

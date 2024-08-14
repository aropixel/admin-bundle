<?php
/**
 * Créé par Aropixel @2017.
 * Par: Joël Gomez Caballe
 * Date: 13/02/2017 à 17:15
 */

namespace Aropixel\AdminBundle\Form\Type\File\Gallery;


use Aropixel\AdminBundle\Entity\File;
use Aropixel\AdminBundle\Form\Type\EntityHiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;


class GalleryFileType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', EntityHiddenType::class, array('class' => File::class))
            ->add('title', HiddenType::class)
            ->add('alt', HiddenType::class)
        ;

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
        $data = $form->getData();

        $fileUrl = null;
        if (null !== $data) {
            $fileUrl = $data->getWebPath();
        }

        // set an "image_url" variable that will be available when rendering this field
        $view->vars['attach_class'] = $form->getConfig()->getDataClass();
        $view->vars['file_url'] = $fileUrl;
        $view->vars['attachedFile'] = $data;

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
        ));
    }


    public function getName()
    {
        return 'aropixel_gallery_file';
    }
}

<?php

namespace Aropixel\AdminBundle\Form\Type\File\Single;

use Aropixel\AdminBundle\Entity\FileInterface;
use Aropixel\AdminBundle\Form\Type\EntityHiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', EntityHiddenType::class, ['class' => FileInterface::class])
            ->add('title', HiddenType::class)
            ->add('alt', HiddenType::class)
        ;
    }

    /**
     * Pass the image URL to the view.
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
        $resolver->setDefaults(['data_class' => null]);
    }

    public function getBlockPrefix()
    {
        return 'aropixel_admin_file';
    }
}

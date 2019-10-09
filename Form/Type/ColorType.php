<?php

namespace Aropixel\AdminBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Aropixel\AdminBundle\Form\DataTransformer\EntityToHiddenTransformer;
use Aropixel\AdminBundle\Form\DataTransformer\MultipleEntityToHiddenTransformer;
use Symfony\Component\Routing\RouterInterface;


class ColorType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }


    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {

        $view->vars['colorFormat'] = $options['format'];

    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'format' => 'hex',
            ))
        ;
    }


    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aropixel_admin_color';
    }

}

<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 04/07/2020 à 19:21
 */

namespace Aropixel\AdminBundle\Form\Type\Page;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimedType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if ($options['createdAt'] !== false) {
            $builder
                ->add('createdAt', DateTimeType::class, array(
                    'label' => "Créé le",
                    'required' => false,
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'date_format' => 'yyyy-MM-dd',
                ))
            ;
        }

        if ($options['updatedAt'] !== false) {
            $builder
                ->add('updatedAt', DateTimeType::class, array(
                    'required' => false,
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                    'date_format' => 'yyyy-MM-dd',
                ))
            ;
        }


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'createdAt' => true,
                'updatedAt' => false,
            ))
        ;
    }

}

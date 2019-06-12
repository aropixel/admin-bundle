<?php
/**
 * Créé par Aropixel @2017.
 * Par: Joël Gomez Caballe
 * Date: 14/02/2017 à 10:09
 */

namespace Aropixel\AdminBundle\Form\Type\Image\Single;

use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Form\Type\EntityHiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AttachImageType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }


    public function getName()
    {
        return 'aropixel_image';
    }
}

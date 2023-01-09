<?php
/**
 * Créé par Aropixel @2017.
 * Par: Joël Gomez Caballe
 * Date: 14/02/2017 à 10:09
 */

namespace Aropixel\AdminBundle\Http\Form\Type\File\Single;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;


class AttachFileType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }


    public function getName()
    {
        return 'aropixel_file';
    }
}

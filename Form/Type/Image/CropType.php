<?php
/**
 * Créé par Aropixel @2017.
 * Par: Joël Gomez Caballe
 * Date: 01/03/2017 à 00:08
 */

namespace Aropixel\AdminBundle\Form\Type\Image;

use Aropixel\AdminBundle\Form\EventListener\DoFileCropListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;


class CropType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('filter', HiddenType::class)
            ->add('crop', HiddenType::class)
        ;
    }




    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'image_class' => null,
        ));
    }



    public function getName()
    {
        return 'aropixel_crop';
    }
}

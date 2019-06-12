<?php
/**
 * Créé par Aropixel @2017.
 * Par: Joël Gomez Caballe
 * Date: 13/02/2017 à 17:15
 */

namespace Aropixel\AdminBundle\Form\Type\Image\Single;


use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Form\Type\EntityHiddenType;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ImageType extends AbstractType
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $em;

    /**
     * @param ObjectManager $om
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }




    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image', EntityHiddenType::class, array('class' => Image::class))
            ->add('title', HiddenType::class)
            ->add('alt', HiddenType::class)
        ;

        if ($options['crop_class']) {

            $builder
                ->add('crops', CropsType::class, array(
                    'entry_options'  => array(
                        'image_class'  => $options['data_class'],
                        'data_class'  => $options['crop_class'],
                    )
                ))
            ;

        }
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

        $imageUrl = null;
        if (null !== $data) {
            $imageUrl = $data->getWebPath();
        }

        // set an "image_url" variable that will be available when rendering this field
        $view->vars['attach_class'] = $form->getConfig()->getDataClass();
        $view->vars['image_url'] = $imageUrl;
        $view->vars['attachedImage'] = $data;
        $view->vars['imageLongClass'] = $options['data_class'];

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'crop_class' => null,
        ));
    }


    public function getName()
    {
        return 'aropixel_image';
    }
}

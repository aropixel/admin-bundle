<?php
namespace Aropixel\AdminBundle\Form\Type\Image\Gallery;


use Aropixel\AdminBundle\Form\Type\Image\Gallery\GalleryCropsType;
use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Entity\ImageInterface;
use Aropixel\AdminBundle\Form\Type\EntityHiddenType;
use Aropixel\AdminBundle\Form\Type\Image\InstanceToData;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Traversable;


//class GalleryImageType extends ImageType
class GalleryImageType extends AbstractType implements DataMapperInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var InstanceToData
     */
    private $instanceToData;


    //
    private $options;
    private $cropSuffix;


    /**
     * @param EntityManagerInterface $em
     * @param InstanceToData $instanceToData
     */
    public function __construct(EntityManagerInterface $em, InstanceToData $instanceToData)
    {
        $this->em = $em;
        $this->instanceToData = $instanceToData;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Build a uniqid used to identify the crop modal
        $this->options = $options;

        //
        $builder
            ->add('title', HiddenType::class)
            ->add('description', HiddenType::class)
            ->add('link', HiddenType::class)
            ->add('attr_title', HiddenType::class)
            ->add('attr_alt', HiddenType::class)
            ->add('attr_class', HiddenType::class)
        ;

        // If property was given, the image is store as a file_name in a custom entity
        if ($options['data_value']) {

            //
            $builder->add('file_name', HiddenType::class);

            //
            if ($options['crops']) {

                $builder
                    ->add('crops', GalleryCropsType::class, array(
                        'image_class'  => $options['data_class'],
                        'image_value'  => $options['data_value'],
                        'image_crops'  => $options['data_crops'],
                        'crops'  => $options['crops'],
                    ))
                ;

            }

            //
            $builder->setDataMapper($this);
        }

        // Otherwise, the image is stored in an AttachImage
        else {

            //
            $builder->add('image', EntityHiddenType::class, array('class' => ImageInterface::class));

            //
            if ($options['crop_class']) {

                $builder
                    ->add('crops', GalleryCropsType::class, array(
                        'image_class'  => $options['data_class'],
                        'entry_options' => array(
                            'data_class'  => $options['crop_class'],
                        )
                    ))
                ;

            }
        }


    }



    /**
     * @param mixed $data
     * @param \Traversable $forms
     */
    public function mapDataToForms($data, Traversable $forms) : void
    {
        // there is no data yet, so nothing to prepopulate
        if (null === $data) {
            return;
        }

        //
        $attributes = $this->instanceToData->getAttributes($data);

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);
        $forms['file_name']->setData($this->instanceToData->getFileName($data));

        //
        if (is_array($attributes) && array_key_exists('link', $attributes)) {
            $forms['link']->setData($attributes['link']);
        }

        //
        if (is_array($attributes) && array_key_exists('title', $attributes)) {
            $forms['title']->setData($attributes['title']);
        }

        //
        if (is_array($attributes) && array_key_exists('description', $attributes)) {
            $forms['description']->setData($attributes['description']);
        }

        //
        if (is_array($attributes) && array_key_exists('attr_title', $attributes)) {
            $forms['attr_title']->setData($attributes['attr_title']);
        }

        //
        if (is_array($attributes) && array_key_exists('attr_alt', $attributes)) {
            $forms['attr_alt']->setData($attributes['attr_alt']);
        }

        //
        if (is_array($attributes) && array_key_exists('attr_class', $attributes)) {
            $forms['attr_class']->setData($attributes['attr_class']);
        }

        //
        if (array_key_exists('crops', $forms)) {

            $crops = $this->instanceToData->getCrops($data);
            if ($crops) {
                $forms['crops']->setData($crops);
            }

        }

    }


    public function mapFormsToData(Traversable $forms, &$viewData): void
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

//        $data = $this->filenameInstance;
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $propertyAccessor->setValue($viewData, $this->options['data_value'], $forms['file_name']->getData());

        if ($this->options['data_crops'] && array_key_exists($this->options['data_crops'], $forms) && !is_null($forms['crops']->getData())) {
            $propertyAccessor->setValue($viewData, $this->options['data_crops'], $forms['crops']->getData());
        }

        if ($this->options['data_attributes']) {

            $attributes = [];

            if (!is_null($forms['title']->getData())) {
                $attributes['title'] = $forms['title']->getData();
            }

            if (!is_null($forms['link']->getData())) {
                $attributes['link'] = $forms['link']->getData();
            }

            if (!is_null($forms['description']->getData())) {
                $attributes['description'] = $forms['description']->getData();
            }

            if (!is_null($forms['attr_title']->getData())) {
                $attributes['attr_title'] = $forms['attr_title']->getData();
            }

            if (!is_null($forms['attr_alt']->getData())) {
                $attributes['attr_alt'] = $forms['attr_alt']->getData();
            }

            if (!is_null($forms['attr_class']->getData())) {
                $attributes['attr_class'] = $forms['attr_class']->getData();
            }

            $propertyAccessor->setValue($viewData, $this->options['data_attributes'], $attributes);
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
        //
        $data = $form->getData();

        //
        if ($options['data_value']) {

            $view->vars['image_path'] = Image::getFileNameWebPath($this->instanceToData->getFileName($data));

        }
        else {

            $imageUrl = null;
            if (null !== $data) {
                $imageUrl = $data->getWebPath();
            }

            // set an "image_url" variable that will be available when rendering this field
            $view->vars['image_path'] = $imageUrl;

        }

        // set an "image_url" variable that will be available when rendering this field
        $view->vars['image_data'] = $data;
        $view->vars['image_class'] = $options['data_class'];
        $view->vars['image_value'] = $options['data_value'];
        $view->vars['grid'] = $options['grid'];

        $view->vars['title_label'] = $options['fields']['title']['label'];
        $view->vars['title_enabled'] = $options['fields']['title']['enabled'];
        $view->vars['description_label'] = $options['fields']['description']['label'];
        $view->vars['description_enabled'] = $options['fields']['description']['enabled'];
        $view->vars['link_label'] = $options['fields']['link']['label'];
        $view->vars['link_enabled'] = $options['fields']['link']['enabled'];

//        $view->vars['library'] = $form->getConfig()->getDataClass();

    }


    private function getFieldOption() {

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(

            'data_class' => null,           // The class name of the image entity (can be an AttachImage entity or a custom entity)
            'data_value' => null,           // If image is stored as a file_name: the property where the file_name is stored in the custom entity
            'data_crops' => null,           // If crops are stored as an array: the property where the crops info are stored in the custom entity
            'data_attributes' => null,      // The property where the attributes (title, alt, css class) are stored in the custom entity
            'fields' => [],                 // The fields to display in the image popup: title, description, link

            'crop_class' => null,           // The class if crops info are stored in a separate entity
            'crops' => null,                // The crops to propose, defined directly in the form configuration

            'grid' => 'col-md-6 col-lg-6',
        ));

    }


    public function getBlockPrefix()
    {
        return 'aropixel_gallery_image';
    }

}

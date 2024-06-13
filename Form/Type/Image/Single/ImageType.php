<?php
namespace Aropixel\AdminBundle\Form\Type\Image\Single;


use Aropixel\AdminBundle\Form\Type\Image\Single\CropsType;
use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Entity\ImageInterface;
use Aropixel\AdminBundle\Form\Type\EntityHiddenType;
use Aropixel\AdminBundle\Form\Type\Image\InstanceToData;
use Aropixel\AdminBundle\Services\ImageManager;
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



/**
 * Class ImageType
 * FormType used to display an image Widget. The widget embed upload tool, library, and crops tool.
 * The image can be stored in an AttachImage entity or as a filename in a custom entity.
 * Crops infos can be stored in a dedicated entity, or as an array in the same custom entity than the image.
 *
 * @package Aropixel\AdminBundle\Form\Type\Image\Single
 */
class ImageType extends AbstractType implements DataMapperInterface
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
    private $normalizedData;

    //
    private $cropSuffix = [];

    //
    private $filenameInstance;
    private $filenameClass;
    private $filenameValue;
    private $cropsValue;


    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em, InstanceToData $instanceToData)
    {
        $this->em = $em;
        $this->instanceToData = $instanceToData;
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Build a uniqid used to identify the crop modal
        $this->cropSuffix[$builder->getName()] = uniqid();


        //
        if (!is_null($options['data_value'])) {

            $this->buildFormFileNameMode($builder, $options);

        }
        else
        {

            $this->buildFormEntityMode($builder, $options);

        }


    }


    /**
     * Build form if ImageType is in "entity mode"
     * The entity mode is used when the image association (between an Image entity and a target entity)
     * is stored in an AttachEntity
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    private function buildFormEntityMode(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image', EntityHiddenType::class, array(
                'class' => ImageInterface::class,
                'required' => $options['required']
            ))
            ->add('attrTitle', HiddenType::class)
            ->add('attrAlt')
        ;

        if (array_key_exists('crop_class', $options) && $options['crop_class']) {
            $builder
                ->add('crops', CropsType::class, array(
                    'entry_options'  => array(
                        'image_class'  => $options['data_class'],
                        'data_class'  => $options['crop_class'],
                    ),
                    'suffix'  => $this->cropSuffix[$builder->getName()],
                    'crops' => $options['crops']
                ))
            ;
        }

    }


    /**
     * Build form if ImageType is in "file name mode"
     * The file name mode is used when the image association (between an Image entity and a target entity)
     * is stored in a field of the custom entity, as a file name.
     * Crops infos can be stored the same way as an array, in a different field of the custom entity.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    private function buildFormFileNameMode(FormBuilderInterface $builder, array $options)
    {

        /**
         * Infos about the custom entity
         * - The class name of the entity
         * - The property name which store file name in the entity
         * - The property name which store crops applied to the image
         */
        $this->filenameClass = $options['data_class'];
        $this->filenameValue = $options['data_value'];
        $this->cropsValue = $options['crops_value'];

        //
        $this->instanceToData->setFilenameValue($options['data_value']);
        $this->instanceToData->setCropsValue($options['crops_value']);



        //
        $builder
            ->add('file_name', HiddenType::class, ['required' => $options['required']])
            ->setDataMapper($this)
        ;


        // Get requested crops
        $crops = $options['crops'] && is_array($options['crops']) ? $options['crops'] : [];
        if (count($crops)) {
            $builder
                ->add('crops', CropsType::class, array(
                    'suffix'  => $this->cropSuffix[$builder->getName()],
                    'image_value'  => $this->filenameValue,
                    'crops_value'  => $this->cropsValue,
                    'crops' => $crops,
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

        // Can be an AttachImage entity, or a file name as a string
        $view->vars['data'] = $data;
        $view->vars['crop_suffix'] = $this->cropSuffix[$form->getName()];
        $view->vars['grid'] = $options['grid'];

        // The entity class in charge to record the image
        if (array_key_exists('data_class', $options) && $options['data_class']) {
            $view->vars['attach_class'] = $options['data_class'];
        }
        elseif (array_key_exists('data_value', $options) && $options['data_value']) {
            $view->vars['attach_class'] = $form->getParent()->getConfig()->getDataClass();
        }

        // The entity class in charge to record the image
        if (array_key_exists('data_value', $options) && $options['data_value']) {
            $view->vars['attach_value'] = $options['data_value'];
            $view->vars['image_value'] = $options['data_value'];
        }

        // If the crops to display was specified in the form configuration
        if ($form->has('crops')) {
            $view->vars['crops'] = $options['crops'];
        }

        //
        if ($options['data_value']) {

            $normalizedData = $this->instanceToData->getFileName($data);
            $view->vars['image_path'] = $normalizedData ? Image::getFileNameWebPath($normalizedData) : '';

        }
        else {

            $imageUrl = null;
            if (null !== $data) {
                $imageUrl = $data->getWebPath();
            }

            // set an "image_url" variable that will be available when rendering this field
            $view->vars['image_path'] = $imageUrl;

        }

        //
        $view->vars['library'] = $options['library'] ?: ($form->getConfig()->getDataClass() ? $form->getConfig()->getDataClass() : '');
        $view->vars['description'] = $options['description'];
        $view->vars['card_footer'] = $options['card_footer'];

    }



    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if (null === $viewData) {
            return;
        }

        $attributes = $this->instanceToData->getAttributes($viewData);
        $forms = iterator_to_array($forms);
        $forms['file_name']->setData($this->instanceToData->getFileName($viewData));

        if (is_array($attributes)) {
            if (array_key_exists('link', $attributes)) {
                $forms['link']->setData($attributes['link']);
            }
            if (array_key_exists('title', $attributes)) {
                $forms['title']->setData($attributes['title']);
            }
            if (array_key_exists('description', $attributes)) {
                $forms['description']->setData($attributes['description']);
            }
            if (array_key_exists('attr_title', $attributes)) {
                $forms['attr_title']->setData($attributes['attr_title']);
            }
            if (array_key_exists('attr_alt', $attributes)) {
                $forms['attr_alt']->setData($attributes['attr_alt']);
            }
            if (array_key_exists('attr_class', $attributes)) {
                $forms['attr_class']->setData($attributes['attr_class']);
            }
        }

        if (array_key_exists('crops', $forms)) {
            $crops = $this->instanceToData->getCrops($viewData);
            if ($crops) {
                $forms['crops']->setData($crops);
            }
        }
    }


    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);
        $config = $forms['file_name']->getParent()->getConfig();
        $dataClass = $config->getDataClass();

        if (!is_null($dataClass)) {
            if (!is_null($viewData)) {
                $propertyAccessor = PropertyAccess::createPropertyAccessor();
                $propertyAccessor->setValue($viewData, $config->getOption('data_value'), $forms['file_name']->getData());

                if (array_key_exists('crops', $forms)) {
                    $propertyAccessor->setValue($viewData, $config->getOption('crops_value'), $forms['crops']->getData());
                }
            }
        } else {
            $viewData = $forms['file_name']->getData();
        }
    }



    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,       // The class name of the image entity (can be an AttachImage entity or a custom entity)
            'data_value' => null,       // The field of the entity that store the file name value
            'crop_class' => null,       // The class that stores the crops of the image
            'crops_value' => 'crops',   // The field of the entity that store the crop value
            'crops' => null,              // The crops defined in the form configuration
            'library' => null,          // The entity for which to display the library
            'grid' => null,             // The description text of the widget image
            'description' => null,      // The description text of the widget image
            'card_footer' => true,      // Display the footer of the widget ?
            'required' => false,        // Is the image required ?
        ));

    }


    public function getBlockPrefix()
    {
        return 'aropixel_admin_image';
    }
}

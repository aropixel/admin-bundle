<?php

namespace Aropixel\AdminBundle\Form\Type\Image\Single;

use Aropixel\AdminBundle\Component\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\AttachedImage;
use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Entity\ImageInterface;
use Aropixel\AdminBundle\Form\DataMapper\ImageMapper;
use Aropixel\AdminBundle\Form\Type\EntityHiddenType;
use Aropixel\AdminBundle\Form\Type\Image\InstanceToData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType used to display an image Widget.
 *
 * This widget includes an upload tool, a media library, and a cropping tool.
 * It can operate in two modes:
 * 1. "Entity mode": The image association is stored in an AttachedImage entity.
 * 2. "File name mode": The image is stored as a simple filename string in a custom entity field.
 *
 * Twig block: aropixel_admin_image_widget
 *
 * Options:
 * - data_class: The class of the entity storing the image (required in entity mode).
 * - data_value: The property name storing the filename (required in filename mode).
 * - crop_class: The class that stores crop information.
 * - crops: An array of available crops (e.g., ['main' => 'Main crop', 'thumbnail' => 'Thumbnail']).
 * - library: The entity name for filtering the media library.
 *
 * Usage example:
 * $builder->add('image', ImageType::class, [
 *     'label' => 'Profile Picture',
 *     'data_class' => UserImage::class,
 *     'crops' => ['avatar' => 'Avatar'],
 * ]);
 */
class ImageType extends AbstractType
{
    /** @var array<string,string>  */
    private array $cropSuffix = [];

    public function __construct(
        private readonly InstanceToData $instanceToData,
        private readonly PathResolverInterface $pathResolver,
        private readonly ImageMapper $imageMapper,
    ) {
    }

    /**
     * @param array<mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Build a uniqid used to identify the crop modal
        $this->cropSuffix[$builder->getName()] = uniqid();

        if (null !== $options['data_value']) {
            $this->buildFormFileNameMode($builder, $options);
        } else {
            $this->buildFormEntityMode($builder, $options);
        }
    }

    /**
     * Build form if ImageType is in "entity mode"
     * The entity mode is used when the image association (between an Image entity and a target entity)
     * is stored in an AttachEntity.

     * @param array{data_class: string, crop_class?: string, required: bool, crops?: null|array<mixed>} $options
     */
    private function buildFormEntityMode(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image', EntityHiddenType::class, ['class' => ImageInterface::class, 'required' => $options['required']])
            ->add('attrTitle', HiddenType::class)
            ->add('attrAlt')
        ;

        if (\array_key_exists('crop_class', $options) && $options['crop_class']) {
            $builder
                ->add('crops', CropsType::class, ['entry_options' => ['image_class' => $options['data_class'], 'data_class' => $options['crop_class']], 'suffix' => $this->cropSuffix[$builder->getName()], 'crops' => $options['crops']])
            ;
        }
    }

    /**
     * Build form if ImageType is in "file name mode"
     * The file name mode is used when the image association (between an Image entity and a target entity)
     * is stored in a field of the custom entity, as a file name.
     * Crops infos can be stored the same way as an array, in a different field of the custom entity.
     *
     * @param array{data_value: string, crops_value: string, required: bool, crops?: null|array<mixed>} $options
     */
    private function buildFormFileNameMode(FormBuilderInterface $builder, array $options): void
    {
        /*
         * Infos about the custom entity
         * - The class name of the entity
         * - The property name which store file name in the entity
         * - The property name which store crops applied to the image
         */
        $filenameValue = $options['data_value'];
        $cropsValue = $options['crops_value'];

        $this->instanceToData->setFilenameValue($options['data_value']);
        $this->instanceToData->setCropsValue($options['crops_value']);

        $builder
            ->add('file_name', HiddenType::class, ['required' => $options['required']])
            ->setDataMapper($this->imageMapper)
        ;

        // Get requested crops
        $crops = $options['crops'] ?: [];
        if (\count($crops)) {
            $builder
                ->add('crops', CropsType::class, ['suffix' => $this->cropSuffix[$builder->getName()], 'image_value' => $filenameValue, 'crops_value' => $cropsValue, 'crops' => $crops])
            ;
        }
    }

    /**
     * Pass the image URL to the view.
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var AttachedImage $data */
        $data = $form->getData();

        // Can be an AttachImage entity, or a file name as a string
        $view->vars['data'] = $data;
        $view->vars['crop_suffix'] = $this->cropSuffix[$form->getName()];
        $view->vars['grid'] = $options['grid'];

        // The entity class in charge to record the image
        if (\array_key_exists('data_class', $options) && $options['data_class']) {
            $view->vars['attach_class'] = $options['data_class'];
        } elseif (\array_key_exists('data_value', $options) && $options['data_value']) {
            $view->vars['attach_class'] = $form->getParent()->getConfig()->getDataClass();
        }

        // The entity class in charge to record the image
        if (\array_key_exists('data_value', $options) && $options['data_value']) {
            $view->vars['attach_value'] = $options['data_value'];
            $view->vars['image_value'] = $options['data_value'];
        }

        // If the crops to display was specified in the form configuration
        if ($form->has('crops')) {
            $view->vars['crops'] = $options['crops'];
        }

        if ($options['data_value']) {
            $normalizedData = $this->instanceToData->getFileName($data);
            $view->vars['image_path'] = $normalizedData ? Image::getFileNameWebPath($normalizedData) : '';
        } else {
            $imageUrl = null;
            if (null !== $data) {
                $imageUrl = $this->pathResolver->getImagePath($data->getImage());
            }

            // set an "image_url" variable that will be available when rendering this field
            $view->vars['image_path'] = $imageUrl;
        }

        $view->vars['library'] = $options['library'] ?: ($form->getConfig()->getDataClass() ?: '');
        $view->vars['description'] = $options['description'];
        $view->vars['card_footer'] = $options['card_footer'];
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            // The class name of the image entity (can be an AttachImage entity or a custom entity)
            'data_value' => null,
            // The field of the entity that store the file name value
            'crop_class' => null,
            // The class that stores the crops of the image
            'crops_value' => 'crops',
            // The field of the entity that store the crop value
            'crops' => null,
            // The crops defined in the form configuration
            'library' => null,
            // The entity for which to display the library
            'grid' => null,
            // The description text of the widget image
            'description' => null,
            // The description text of the widget image
            'card_footer' => true,
            // Display the footer of the widget ?
            'required' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_image';
    }
}

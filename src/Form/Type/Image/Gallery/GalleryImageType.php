<?php

namespace Aropixel\AdminBundle\Form\Type\Image\Gallery;

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
 * FormType used for each individual image in a gallery.
 *
 * This type is usually used as an 'entry_type' within GalleryType.
 *
 * Twig block: aropixel_admin_gallery_image_row
 *
 * Options:
 * - data_class: The entity class for the image.
 * - data_value: The property name storing the filename (in filename mode).
 * - fields: Configuration for additional fields (title, description, link).
 * - crop_class: The class that stores crop information.
 */
class GalleryImageType extends AbstractType
{
    public function __construct(
        private readonly InstanceToData $instanceToData,
        private readonly PathResolverInterface $pathResolver,
        private readonly ImageMapper $imageMapper,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', HiddenType::class)
            ->add('description', HiddenType::class)
            ->add('link', HiddenType::class)
            ->add('attrTitle', HiddenType::class)
            ->add('attrAlt')
            ->add('attrClass', HiddenType::class)
        ;

        // If property was given, the image is store as a file_name in a custom entity
        if ($options['data_value']) {
            $builder->add('file_name', HiddenType::class);

            if ($options['crops']) {
                $builder
                    ->add('crops', GalleryCropsType::class, ['image_class' => $options['data_class'], 'image_value' => $options['data_value'], 'image_crops' => $options['data_crops'], 'crops' => $options['crops']])
                ;
            }

            $builder->setDataMapper($this->imageMapper);
        }

        // Otherwise, the image is stored in an AttachImage
        else {
            $builder->add('image', EntityHiddenType::class, ['class' => ImageInterface::class]);

            if ($options['crop_class']) {
                $builder
                    ->add('crops', GalleryCropsType::class, ['image_class' => $options['data_class'], 'entry_options' => ['data_class' => $options['crop_class']]])
                ;
            }
        }
    }

    /**
     * Pass the image URL to the view.
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var AttachedImage $data */
        $data = $form->getData();

        if ($options['data_value']) {
            $view->vars['image_path'] = Image::getFileNameWebPath($this->instanceToData->getFileName($data));
        } else {
            $imageUrl = null;
            if (null !== $data) {
                $imageUrl = $this->pathResolver->getImagePath($data->getImage());
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            // The class name of the image entity (can be an AttachImage entity or a custom entity)
            'data_value' => null,
            // If image is stored as a file_name: the property where the file_name is stored in the custom entity
            'data_crops' => null,
            // If crops are stored as an array: the property where the crops info are stored in the custom entity
            'data_attributes' => null,
            // The property where the attributes (title, alt, css class) are stored in the custom entity
            'fields' => [],
            // The fields to display in the image popup: title, description, link
            'crop_class' => null,
            // The class if crops info are stored in a separate entity
            'crops' => null,
            // The crops to propose, defined directly in the form configuration
            'grid' => 'col-md-6 col-lg-6',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_gallery_image';
    }
}

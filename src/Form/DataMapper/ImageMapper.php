<?php

namespace Aropixel\AdminBundle\Form\DataMapper;

use Aropixel\AdminBundle\Form\Type\Image\InstanceToData;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * DataMapper for ImageType and GalleryImageType.
 *
 * This mapper handles the transfer of data between the image form fields
 * and the underlying data object (either an Entity or a simple string/array).
 */
class ImageMapper implements DataMapperInterface
{
    public function __construct(
        private readonly InstanceToData $instanceToData,
    ) {
    }

    /**
     * Maps the Data object to the Form fields.
     *
     * @param mixed $viewData The object or value to map from.
     * @param \Traversable $forms The form fields to map to.
     */
    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        // If no data is provided, there's nothing to map.
        if (null === $viewData) {
            return;
        }

        // Extract attributes and file name using the helper service.
        $attributes = $this->instanceToData->getAttributes($viewData);
        $forms = iterator_to_array($forms);
        $forms['file_name']->setData($this->instanceToData->getFileName($viewData));

        // Map optional attributes if they are present in the data and the form.
        if (\is_array($attributes)) {
            foreach (['link', 'title', 'description', 'attrTitle', 'attrAlt', 'attrClass'] as $field) {
                if (\array_key_exists($field, $attributes) && isset($forms[$field])) {
                    $forms[$field]->setData($attributes[$field]);
                }
            }
        }

        // Map crops if the field exists in the form and data.
        if (\array_key_exists('crops', $forms)) {
            $crops = $this->instanceToData->getCrops($viewData);
            if ($crops) {
                $forms['crops']->setData($crops);
            }
        }
    }

    /**
     * Maps the Form fields back to the Data object.
     *
     * @param \Traversable $forms The form fields to map from.
     * @param mixed $viewData The object or value to map to.
     */
    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $forms = iterator_to_array($forms);
        $config = $forms['file_name']->getParent()->getConfig();
        $dataClass = $config->getDataClass();

        // If a data_class is defined, we are working with an object.
        if (null !== $dataClass) {
            if (null !== $viewData) {
                $propertyAccessor = PropertyAccess::createPropertyAccessor();

                // Set the main file name property.
                $propertyAccessor->setValue($viewData, $config->getOption('data_value'), $forms['file_name']->getData());

                // Set the crops property (supports different option names used by Single/Gallery types).
                if (\array_key_exists('crops', $forms)) {
                    if ($config->hasOption('crops_value')) {
                        $propertyAccessor->setValue($viewData, $config->getOption('crops_value'), $forms['crops']->getData());
                    } elseif ($config->hasOption('data_crops')) {
                        $propertyAccessor->setValue($viewData, $config->getOption('data_crops'), $forms['crops']->getData());
                    }
                }

                // Map additional attributes if enabled in the form options.
                if ($config->hasOption('data_attributes') && $config->getOption('data_attributes')) {
                    $attributes = [];

                    foreach (['title', 'link', 'description', 'attrTitle', 'attrAlt', 'attrClass'] as $field) {
                        if (isset($forms[$field]) && null !== $forms[$field]->getData()) {
                            $attributes[$field] = $forms[$field]->getData();
                        }
                    }

                    $propertyAccessor->setValue($viewData, $config->getOption('data_attributes'), $attributes);
                }
            }
        } else {
            // If no data_class, viewData is treated as the file name directly.
            $viewData = $forms['file_name']->getData();
        }
    }
}

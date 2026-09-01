<?php

namespace Aropixel\AdminBundle\Form\Type\Image;

use Symfony\Component\PropertyAccess\PropertyAccess;

class InstanceToData
{
    private string $filenameValue = 'value';

    private string $attributesValue = 'attributes';

    private string $cropsValue = 'crops';

    public function setFilenameValue(mixed $filenameValue): static
    {
        $this->filenameValue = $filenameValue;

        return $this;
    }

    public function setCropsValue(mixed $cropsValue): static
    {
        $this->cropsValue = $cropsValue;

        return $this;
    }

    public function getFileName(mixed $data): mixed
    {
        $value = $data;

        // invalid data type
        if ($data && !\is_string($data)) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $value = $propertyAccessor->getValue($data, $this->filenameValue);
        }

        return $value;
    }

    /*
     * Attributes and crops are optional: ImageMapper guards their use, and ImageType
     * accepts arbitrary data objects (only `filename_value` is configurable). Reading
     * them strictly broke every integration passing a domain object without an
     * `attributes` property — v2 only read attributes in the gallery, whose items
     * always have one. Tolerant read: null when the path is not readable.
     */
    public function getAttributes(mixed $data): mixed
    {
        $value = $data;

        // invalid data type
        if ($data && !\is_string($data)) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $value = $propertyAccessor->isReadable($data, $this->attributesValue)
                ? $propertyAccessor->getValue($data, $this->attributesValue)
                : null;
        }

        return $value;
    }

    public function getCrops(mixed $data): mixed
    {
        $value = null;

        // invalid data type
        if (!\is_string($data)) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $value = $propertyAccessor->isReadable($data, $this->cropsValue)
                ? $propertyAccessor->getValue($data, $this->cropsValue)
                : null;
        }

        return $value;
    }
}

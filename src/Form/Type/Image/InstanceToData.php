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

    public function getAttributes(mixed $data): mixed
    {
        $value = $data;

        // invalid data type
        if ($data && !\is_string($data)) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $value = $propertyAccessor->getValue($data, $this->attributesValue);
        }

        return $value;
    }

    public function getCrops(mixed $data): mixed
    {
        $value = null;

        // invalid data type
        if (!\is_string($data)) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $value = $propertyAccessor->getValue($data, $this->cropsValue);
        }

        return $value;
    }
}

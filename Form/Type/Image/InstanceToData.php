<?php

namespace Aropixel\AdminBundle\Form\Type\Image;

use Symfony\Component\PropertyAccess\PropertyAccess;

class InstanceToData
{
    private $filenameValue = 'value';

    private $attributesValue = 'attributes';

    private $cropsValue = 'crops';

    /**
     * @return InstanceToData
     */
    public function setFilenameValue(mixed $filenameValue)
    {
        $this->filenameValue = $filenameValue;

        return $this;
    }

    /**
     * @return InstanceToData
     */
    public function setCropsValue(mixed $cropsValue)
    {
        $this->cropsValue = $cropsValue;

        return $this;
    }

    /**
     * Get file name from an entity.
     *
     * @return mixed|null
     */
    public function getFileName($data)
    {
        $value = $data;

        // invalid data type
        if ($data && !\is_string($data)) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $value = $propertyAccessor->getValue($data, $this->filenameValue);
        }

        return $value;
    }

    /**
     * Get attributes from an entity.
     *
     * @return mixed|null
     */
    public function getAttributes($data)
    {
        $value = $data;

        // invalid data type
        if ($data && !\is_string($data)) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $value = $propertyAccessor->getValue($data, $this->attributesValue);
        }

        return $value;
    }

    /**
     * @return mixed|null
     */
    public function getCrops($data)
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

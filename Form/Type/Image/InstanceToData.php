<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 10/07/2020 à 12:35
 */

namespace Aropixel\AdminBundle\Form\Type\Image;


use Symfony\Component\PropertyAccess\PropertyAccess;

class InstanceToData
{

    /**
     * @var
     */
    private $filenameValue = 'value';

    /**
     * @var
     */
    private $attributesValue = 'attributes';

    /**
     * @var
     */
    private $cropsValue = 'crops';



    /**
     * @param mixed $filenameValue
     * @return InstanceToData
     */
    public function setFilenameValue($filenameValue)
    {
        $this->filenameValue = $filenameValue;
        return $this;
    }



    /**
     * @param mixed $cropsValue
     * @return InstanceToData
     */
    public function setCropsValue($cropsValue)
    {
        $this->cropsValue = $cropsValue;
        return $this;
    }


    /**
     * Get file name from an entity
     * @param $data
     * @return mixed|null
     */
    public function getFileName($data)
    {

        //
        $value = $data;

        // invalid data type
        if ($data && !is_string($data)) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $value = $propertyAccessor->getValue($data, $this->filenameValue);
        }

        return $value;
    }


    /**
     * Get attributes from an entity
     * @param $data
     * @return mixed|null
     */
    public function getAttributes($data)
    {

        //
        $value = $data;

        // invalid data type
        if ($data && !is_string($data)) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $value = $propertyAccessor->getValue($data, $this->attributesValue);
        }

        return $value;
    }


    /**
     * @param $data
     * @return mixed|null
     */
    public function getCrops($data)
    {
        //
        $value = null;

        // invalid data type
        if (!is_string($data)) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            $value = $propertyAccessor->getValue($data, $this->cropsValue);
        }

        return $value;
    }


}

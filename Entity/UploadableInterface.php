<?php

namespace Aropixel\AdminBundle\Entity;

interface UploadableInterface
{
    public function preUpload();

    public function upload();

    public function removeUpload();
}

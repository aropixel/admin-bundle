<?php

namespace Aropixel\AdminBundle\Entity;

interface UploadableInterface
{
    public function preUpload(): void;

    public function upload(): void;

    public function removeUpload(): void;
}

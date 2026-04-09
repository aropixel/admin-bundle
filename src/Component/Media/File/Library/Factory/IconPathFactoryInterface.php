<?php

namespace Aropixel\AdminBundle\Component\Media\File\Library\Factory;

interface IconPathFactoryInterface
{
    public function getIconPath(string $extension): string;
}

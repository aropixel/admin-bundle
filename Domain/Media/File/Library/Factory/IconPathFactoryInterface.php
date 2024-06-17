<?php

namespace Aropixel\AdminBundle\Domain\Media\File\Library\Factory;

interface IconPathFactoryInterface
{
    public function getIconPath(string $extension): string;
}

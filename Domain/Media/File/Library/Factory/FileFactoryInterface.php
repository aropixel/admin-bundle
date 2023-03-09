<?php

namespace Aropixel\AdminBundle\Domain\Media\File\Library\Factory;

use Aropixel\AdminBundle\Entity\ItemLibraryInterface;

interface FileFactoryInterface
{
    public function create() : ItemLibraryInterface;
}

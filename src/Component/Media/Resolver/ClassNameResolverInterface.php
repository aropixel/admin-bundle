<?php

namespace Aropixel\AdminBundle\Component\Media\Resolver;

interface ClassNameResolverInterface
{
    public function getImageClassName(): string;

    public function getFileClassName(): string;
}

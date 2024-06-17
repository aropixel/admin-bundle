<?php

namespace Aropixel\AdminBundle\Domain\Media\Resolver;

interface ClassNameResolverInterface
{
    public function getImageClassName(): string;

    public function getFileClassName(): string;
}

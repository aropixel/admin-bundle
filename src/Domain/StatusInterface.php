<?php

namespace Aropixel\AdminBundle\Domain;

interface StatusInterface
{
    public function changeStatus(object $entity): void;

    public function setProperty(string $property): self;

    public function setValues(string $valueOff, string $valueOn): self;
}

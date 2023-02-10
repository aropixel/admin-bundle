<?php

namespace Aropixel\AdminBundle\Domain\Status;

use Symfony\Component\HttpFoundation\Response;
use Aropixel\AdminBundle\Infrastructure\Status\Status;

interface StatusInterface
{

    public function setProperty(string $property) : Status;
    public function setValues(string $valueOff, string $valueOn) : Status;
    public function changeStatus(object $entity) : Response;

}
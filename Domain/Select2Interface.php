<?php

namespace Aropixel\AdminBundle\Domain;

use Symfony\Component\HttpFoundation\Response;

interface Select2Interface
{
    public function setRepository(string $fqClassName): self;

    public function getItems(): iterable;

    public function getResponse(array $items): Response;
}

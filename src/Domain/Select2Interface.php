<?php

namespace Aropixel\AdminBundle\Domain;

use Symfony\Component\HttpFoundation\Response;

interface Select2Interface
{
    public function setRepository(string $fqClassName): self;

    /**
     * @return mixed[]
     */
    public function getItems(): iterable;

    /**
     * @param array<mixed> $items
     */
    public function getResponse(array $items): Response;
}

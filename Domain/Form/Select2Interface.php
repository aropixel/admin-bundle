<?php

namespace App\Aropixel\AdminBundle\Domain\Form;

use Symfony\Component\HttpFoundation\Response;

interface Select2Interface
{

    public function getResponse(iterable $items) : Response;

}
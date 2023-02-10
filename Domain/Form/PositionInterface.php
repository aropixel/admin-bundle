<?php

namespace App\Aropixel\AdminBundle\Domain\Form;


use Symfony\Component\HttpFoundation\Response;

interface PositionInterface
{

    public function updatePosition(string $repositoryName) : Response;

    public function updateSinglePosition(string $repositoryName, object $entity, int $position) : void;

}
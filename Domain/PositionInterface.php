<?php

namespace Aropixel\AdminBundle\Domain;

interface PositionInterface
{
    public function updatePosition(string $className) : void;
    public function updateSinglePosition(string $className, int $id, int $position) : void;
}

<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 01/02/2023 à 16:34
 */

namespace Aropixel\AdminBundle\Domain\User;

use Aropixel\AdminBundle\Entity\User;

interface UserFactoryInterface
{
    public function createUser() : User;
}

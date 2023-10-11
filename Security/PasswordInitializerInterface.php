<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aropixel\AdminBundle\Security;

use Aropixel\AdminBundle\Entity\UserInterface;

interface PasswordInitializerInterface
{
    public function createPassword(UserInterface $user);
    public function stillPendingPasswordCreation(UserInterface $user) : bool;

}

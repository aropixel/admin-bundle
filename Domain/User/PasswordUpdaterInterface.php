<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aropixel\AdminBundle\Domain\User;

use Aropixel\AdminBundle\Entity\User;

interface PasswordUpdaterInterface
{
    public function hashPlainPassword(User $user);

}

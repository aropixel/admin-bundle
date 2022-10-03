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
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * Class updating the hashed password in the user when there is a new password.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class PasswordUpdater implements PasswordUpdaterInterface
{
    private $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function hashPassword(UserInterface $user)
    {
        $plainPassword = $user->getPlainPassword();

        if (0 === strlen($plainPassword)) {
            return;
        }

        $encoder = $this->encoderFactory->getEncoder($user);
        $salt = rtrim(str_replace('+', '.', base64_encode(random_bytes(32))), '=');
        $hashedPassword = $encoder->encodePassword($plainPassword, $salt);
        $user->setPassword($hashedPassword);
        $user->setLastPasswordUpdate(new \Datetime('now'));
        $user->setLastLogin(null);
        $user->eraseCredentials();
    }
}

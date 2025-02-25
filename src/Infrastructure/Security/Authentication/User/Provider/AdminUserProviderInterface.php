<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Authentication\User\Provider;

use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @extends UserProviderInterface<UserInterface>
 */
interface AdminUserProviderInterface extends UserProviderInterface
{
}

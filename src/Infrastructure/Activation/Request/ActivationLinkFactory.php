<?php

namespace Aropixel\AdminBundle\Infrastructure\Activation\Request;

use Aropixel\AdminBundle\Domain\Activation\Request\ActivationLinkFactoryInterface;
use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class ActivationLinkFactory implements ActivationLinkFactoryInterface
{
    public function __construct(
        private readonly RouterInterface $router
    ) {
    }

    public function createActivationLink(UserInterface $user): string
    {
        return $this->router->generate('aropixel_admin_create_password', ['token' => $user->getPasswordResetToken()], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

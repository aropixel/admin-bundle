<?php

namespace Aropixel\AdminBundle\Infrastructure\Activation\Request;

use Aropixel\AdminBundle\Domain\Activation\Request\ActivationLinkFactoryInterface;
use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class ActivationLinkFactory implements ActivationLinkFactoryInterface
{
    private RouterInterface $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function createActivationLink(UserInterface $user): string
    {
        return $this->router->generate('aropixel_admin_create_password', ['token' => $user->getPasswordResetToken()], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

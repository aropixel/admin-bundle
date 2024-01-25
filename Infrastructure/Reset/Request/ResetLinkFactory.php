<?php

namespace Aropixel\AdminBundle\Infrastructure\Reset\Request;

use Aropixel\AdminBundle\Domain\Reset\Request\ResetLinkFactoryInterface;
use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class ResetLinkFactory implements ResetLinkFactoryInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function createResetLink(UserInterface $user): string
    {
        return $this->router->generate('aropixel_admin_reset_password', ['token' => $user->getPasswordResetToken()], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

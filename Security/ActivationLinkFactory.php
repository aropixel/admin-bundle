<?php

namespace Aropixel\AdminBundle\Security;

use Aropixel\AdminBundle\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class ActivationLinkFactory implements ActivationLinkFactoryInterface
{
    /** @var RouterInterface  */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function createActivationLink(User $user): string
    {
        return $this->router->generate('aropixel_admin_create_password', ['token' => $user->getPasswordResetToken()], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

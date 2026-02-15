<?php

namespace Aropixel\AdminBundle\Component\Security\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface AuthenticationSuccessHandlerInterface
{
    public function handleSuccess(Request $request, TokenInterface $token, string $firewallName): Response;
}

<?php

namespace Aropixel\AdminBundle\Infrastructure\Security;

use Aropixel\AdminBundle\Infrastructure\Security\Handler\AuthenticationFailureHandlerInterface;
use Aropixel\AdminBundle\Infrastructure\Security\Handler\AuthenticationSuccessHandlerInterface;
use Aropixel\AdminBundle\Infrastructure\Security\Passport\Factory\PassportFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    public function __construct(
        private readonly AuthenticationFailureHandlerInterface $authenticationFailureHandler,
        private readonly AuthenticationSuccessHandlerInterface $authenticationSuccessHandler,
        private readonly PassportFactoryInterface $passportFactory,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('aropixel_admin_security_login');
    }

    public function authenticate(Request $request): Passport
    {
        return $this->passportFactory->createPassport($request);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return $this->authenticationSuccessHandler->handleSuccess($request, $token, $firewallName);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        parent::onAuthenticationFailure($request, $exception);

        return $this->authenticationFailureHandler->handleFailure($request, $exception);
    }
}

<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Authentication\Credentials;

use Aropixel\AdminBundle\Infrastructure\Security\Authentication\Credentials\CredentialsResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class CredentialsResolver implements CredentialsResolverInterface
{
    public function __construct(
        private readonly AuthenticationUtils $authenticationUtils
    ) {
    }

    public function getCredentials(Request $request): array
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
            'g-recaptcha-response' => $request->request->get('g-recaptcha-response', null),
        ];

        $request->getSession()->set(
            $this->authenticationUtils->getLastUsername(),
            $credentials['email']
        );

        return $credentials;
    }
}

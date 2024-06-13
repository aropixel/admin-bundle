<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Authentication\Credentials;

use Aropixel\AdminBundle\Infrastructure\Security\Authentication\Credentials\CredentialsResolverInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

class CredentialsResolver implements CredentialsResolverInterface
{
    private $authenticationUtils;

    public function __construct(AuthenticationUtils $authenticationUtils)
    {
        $this->authenticationUtils = $authenticationUtils;
    }


    public function getCredentials(Request $request): array
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
            'g-recaptcha-response' => $request->request->get('g-recaptcha-response', null)
        ];

        $request->getSession()->set(
            $this->authenticationUtils->getLastUsername(),
            $credentials['email']
        );

        return $credentials;
    }
}
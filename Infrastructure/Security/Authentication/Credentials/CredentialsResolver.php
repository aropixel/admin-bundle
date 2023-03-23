<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Authentication\Credentials;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;

class CredentialsResolver implements CredentialsResolverInterface
{

    public function getCredentials(Request $request): array
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
            'g-recaptcha-response' => $request->request->get('g-recaptcha-response', null)
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }
}

<?php

namespace Aropixel\AdminBundle\Http\Action\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginAction extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;

    /**
     * @param AuthenticationUtils $authenticationUtils
     */
    public function __construct(AuthenticationUtils $authenticationUtils)
    {
        $this->authenticationUtils = $authenticationUtils;
    }

    public function __invoke() : Response
    {
        // get the login error if there is one
        $error = $this->authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->render('@AropixelAdmin/Security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

}
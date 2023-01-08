<?php

namespace Aropixel\AdminBundle\Http\Action\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LogoutAction extends AbstractController
{
    public function __invoke() : Response
    {
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

}
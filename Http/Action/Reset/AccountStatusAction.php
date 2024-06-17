<?php

namespace Aropixel\AdminBundle\Http\Action\Reset;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountStatusAction extends AbstractController
{
    public const ATTEMPTS = 'attempts';
    public const LOGIN = 'login';
    public const PASSWORD = 'password';

    public function __invoke(string $status)
    {
        $views = [
            self::ATTEMPTS => '@AropixelAdmin/Reset/blocked_request_info.html.twig',
            self::LOGIN => '@AropixelAdmin/Reset/too_old_last_login_request_info.html.twig',
            self::PASSWORD => '@AropixelAdmin/Reset/too_old_password_request_info.html.twig',
        ];

        if (!\array_key_exists($status, $views)) {
            throw $this->createNotFoundException();
        }

        return $this->render($views[$status]);
    }
}

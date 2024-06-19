<?php

namespace Aropixel\AdminBundle\Http\Action\Reset;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RequestStatusAction extends AbstractController
{
    public const PENDING = 'pending';
    public const EXPIRED = 'expired';
    public const SUCCESS = 'success';

    public function __invoke(string $status)
    {
        $views = [
            self::PENDING => '@AropixelAdmin/Reset/request_info.html.twig',
            self::EXPIRED => '@AropixelAdmin/Reset/request_expired.html.twig',
            self::SUCCESS => '@AropixelAdmin/Reset/reset_success.html.twig',
        ];

        if (!\array_key_exists($status, $views)) {
            throw $this->createNotFoundException();
        }

        return $this->render($views[$status]);
    }
}

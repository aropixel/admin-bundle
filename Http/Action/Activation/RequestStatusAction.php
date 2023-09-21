<?php

namespace Aropixel\AdminBundle\Http\Action\Activation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RequestStatusAction extends AbstractController
{
    const EXPIRED = 'expired';
    const SUCCESS = 'success';

    public function __invoke(string $status)
    {
        $views = [
            self::EXPIRED => '@AropixelAdmin/Activation/request_expired.html.twig',
            self::SUCCESS => '@AropixelAdmin/Activation/success.html.twig',
        ];

        if (!array_key_exists($status, $views)) {
            throw $this->createNotFoundException();
        }

        return $this->render($views[$status]);
    }

}
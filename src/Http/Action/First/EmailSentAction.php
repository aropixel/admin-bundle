<?php

namespace Aropixel\AdminBundle\Http\Action\First;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class EmailSentAction extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('@AropixelAdmin/First/email_sent.html.twig');
    }
}

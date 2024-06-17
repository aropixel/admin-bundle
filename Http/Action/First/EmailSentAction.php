<?php

namespace Aropixel\AdminBundle\Http\Action\First;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmailSentAction extends AbstractController
{
    public function __invoke()
    {
        return $this->render('@AropixelAdmin/First/email_sent.html.twig');
    }
}

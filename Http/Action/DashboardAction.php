<?php

namespace Aropixel\AdminBundle\Http\Action;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardAction extends AbstractController
{
    public function __invoke()
    {
        return $this->render('@AropixelAdmin/dashboard.html.twig');
    }

}

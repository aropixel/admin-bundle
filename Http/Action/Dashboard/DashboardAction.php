<?php

namespace Aropixel\AdminBundle\Http\Action\Dashboard;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardAction extends AbstractController
{

    public function __invoke() : Response
    {
        return $this->render('@AropixelAdmin/dashboard.html.twig', []);
    }

}
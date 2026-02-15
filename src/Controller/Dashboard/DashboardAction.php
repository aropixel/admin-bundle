<?php

namespace Aropixel\AdminBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DashboardAction extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('@AropixelAdmin/dashboard.html.twig', []);
    }
}

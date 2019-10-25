<?php

namespace Aropixel\AdminBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class DefaultController extends AbstractController
{

    /**
     * @Route("/", name="_admin")
     */
    public function indexAction()
    {

        return $this->render('@AropixelAdmin/dashboard.html.twig', array());

    }


}

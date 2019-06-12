<?php

namespace Aropixel\AdminBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

// these import the "@Route" and "@Template" annotations
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\Role;



class DefaultController extends Controller
{

    /**
     * @Route("/", name="_admin")
     */
    public function indexAction()
    {

        return $this->render('@AropixelAdmin/dashboard.html.twig', array());

    }


}

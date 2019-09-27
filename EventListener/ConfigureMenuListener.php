<?php
// src/AppBundle/EventListener/ConfigureMenuListener.php

namespace Aropixel\AdminBundle\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;
use Aropixel\AdminBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{

    protected $requestStack;


    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }


}

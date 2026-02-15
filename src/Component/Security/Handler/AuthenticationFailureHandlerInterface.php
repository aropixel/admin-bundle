<?php

namespace Aropixel\AdminBundle\Component\Security\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

interface AuthenticationFailureHandlerInterface
{
    public function handleFailure(Request $request, AuthenticationException $exception): Response;
}

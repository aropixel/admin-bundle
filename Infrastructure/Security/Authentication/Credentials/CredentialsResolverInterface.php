<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Authentication\Credentials;

use Symfony\Component\HttpFoundation\Request;

interface CredentialsResolverInterface
{
    public function getCredentials(Request $request) : array;
}
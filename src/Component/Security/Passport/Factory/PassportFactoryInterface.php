<?php

namespace Aropixel\AdminBundle\Component\Security\Passport\Factory;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

interface PassportFactoryInterface
{
    public function createPassport(Request $request): Passport;
}

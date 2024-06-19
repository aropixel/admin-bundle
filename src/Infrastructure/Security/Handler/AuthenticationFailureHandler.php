<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Handler;

use Aropixel\AdminBundle\Domain\Reset\Request\RequestLauncherInterface;
use Aropixel\AdminBundle\Infrastructure\Security\Authentication\Credentials\CredentialsResolverInterface;
use Aropixel\AdminBundle\Infrastructure\Security\Authentication\User\Provider\AdminUserProviderInterface;
use Aropixel\AdminBundle\Infrastructure\Security\Exception\TooOldLastLoginException;
use Aropixel\AdminBundle\Infrastructure\Security\Exception\TooOldPasswordException;
use Aropixel\AdminBundle\Infrastructure\Security\Handler\AuthenticationFailureHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function __construct(
        private readonly AdminUserProviderInterface $userProvider,
        private readonly CredentialsResolverInterface $credentialsResolver,
        private readonly EntityManagerInterface $em,
        private readonly ParameterBagInterface $parameterBag,
        private readonly RequestLauncherInterface $requestLauncher,
        private readonly RouterInterface $router
    ) {
    }

    public function handleFailure(Request $request, AuthenticationException $exception): Response
    {
        $credentials = $this->credentialsResolver->getCredentials($request);
        $user = $this->userProvider->loadUserByIdentifier($credentials['email']);

        if ($user) {
            $newPasswordAttempts = $user->getPasswordAttempts() + 1;
            $user->setPasswordAttempts($newPasswordAttempts);
            $this->em->flush();

            $maxPasswordAttempts = $this->parameterBag->get('passwordAttempts');

            if ($newPasswordAttempts >= $maxPasswordAttempts) {
                $this->requestLauncher->reset($user);

                return new RedirectResponse($this->router->generate('aropixel_admin_account_status', ['status' => 'attempts']));
            }
        }

        if ($exception instanceof TooOldPasswordException) {
            $this->requestLauncher->reset($user);

            return new RedirectResponse($this->router->generate('aropixel_admin_account_status', ['status' => 'password']));
        }

        if ($exception instanceof TooOldLastLoginException) {
            $this->requestLauncher->reset($user);

            return new RedirectResponse($this->router->generate('aropixel_admin_account_status', ['status' => 'login']));
        }

        return new RedirectResponse($this->router->generate('aropixel_admin_security_login'));
    }
}

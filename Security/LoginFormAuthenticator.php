<?php

namespace Aropixel\AdminBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class LoginFormAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    protected $entityManager;
    protected $parameterBag;
    protected $urlGenerator;
    protected $csrfTokenManager;
    protected $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordHasher = $passwordHasher;
    }

    public function supports(Request $request): ?bool
    {
        return 'aropixel_admin_security_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }


    public function authenticate(Request $request): Passport
    {
        $password = $request->request->get('password');
        $email = $request->request->get('email');

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('_admin'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $entities = $this->parameterBag->get('aropixel_admin.entities');
        $user = $this->entityManager->getRepository($entities[\Aropixel\AdminBundle\Entity\UserInterface::class])->findOneBy(['email' => $credentials['email']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }

        if (!$user->isEnabled()) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('User is not enabled.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordHasher->isPasswordValid($user, $credentials['password']);
    }


    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('aropixel_admin_security_login');
    }
}

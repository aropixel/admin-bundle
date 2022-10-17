<?php

namespace Aropixel\AdminBundle\Security;

use Aropixel\AdminBundle\Security\Exception\TooOldPasswordException;
use Aropixel\AdminBundle\Security\Exception\TooOldLastLoginException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Event\AuthenticationTokenCreatedEvent;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Aropixel\AdminBundle\Security\Passport\Badge\TooOldPasswordBadge;
use Aropixel\AdminBundle\Security\Passport\Badge\TooOldLastLoginBadge;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    protected $csrfTokenManager;
    protected $parameterBag;
    protected $entityManager;
    protected $userProvider;
    protected $urlGenerator;
    protected $passwordHasher;


    public function __construct(
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        UserPasswordHasherInterface $passwordHasher,
        UserProviderInterface $userProvider,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $this->csrfTokenManager = $csrfTokenManager;
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->passwordHasher = $passwordHasher;
        $this->userProvider = $userProvider;
        $this->urlGenerator = $urlGenerator;
    }

    public function supports(Request $request): bool
    {
        return 'aropixel_admin_security_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
            'g-recaptcha-response' => $request->request->get('g-recaptcha-response', null)
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function authenticate(Request $request) : Passport
    {
        $credentials = $this->getCredentials($request);

        $user = $this->getUser($credentials);

        $passport = new Passport(
            new UserBadge($credentials['email'], [$this->userProvider, 'loadUserByIdentifier']),
            new PasswordCredentials($credentials['password']),
            [new RememberMeBadge()]
        );

        $passport->addBadge(new TooOldPasswordBadge($user, $this->parameterBag));
        $passport->addBadge(new TooOldLastLoginBadge($user));

        // Ajout d'un badge de gestion du Csrf
        $passport->addBadge(new CsrfTokenBadge('authenticate', $credentials['csrf_token']));

        if ($this->userProvider instanceof PasswordUpgraderInterface) {
            $passport->addBadge(new PasswordUpgradeBadge($credentials['password'], $this->userProvider));
        }

        return $passport;
    }

    public function getUser($credentials)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $entities = $this->parameterBag->get('aropixel_admin.entities');
        $user = $this->entityManager->getRepository($entities[\Aropixel\AdminBundle\Entity\UserInterface::class])->findOneBy(['email' => $credentials['email']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new BadCredentialsException('Invalid credentials.');
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

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        $user = $token->getUser();
        $user->setPasswordAttempts(0);
        $user->setLastLogin(new \DateTime('now'));
        $this->entityManager->flush();

        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        // For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
        return new RedirectResponse($this->urlGenerator->generate('_admin'));
//        throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }

        $credentials = $this->getCredentials($request);
        $user = $this->getUser($credentials);

        if ($user) {
            $newPasswordAttempts = $user->getPasswordAttempts() +1;
            $user->setPasswordAttempts($newPasswordAttempts);
            $this->entityManager->flush();

            $maxPasswordAttempts = $this->parameterBag->get('passwordAttempts');

            if ($newPasswordAttempts >= $maxPasswordAttempts) {
                return new RedirectResponse($this->urlGenerator->generate('aropixel_admin_blocked_reset_password', ['userId' => $user->getId()]));
            }
        }

        if ($exception instanceof TooOldPasswordException) {
            $userId = $exception->getUser()->getId();
            return new RedirectResponse($this->urlGenerator->generate('aropixel_admin_too_old_password_reset_password', ['userId' => $userId]));
        }

        if ($exception instanceof TooOldLastLoginException) {
            $userId = $exception->getUser()->getId();
            return new RedirectResponse($this->urlGenerator->generate('aropixel_admin_too_old_last_login_reset_password', ['userId' => $userId]));
        }

        $url = $this->getLoginUrl($request);

        return new RedirectResponse($url);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('aropixel_admin_security_login');
    }

}

<?php

namespace Aropixel\AdminBundle\Security;

use Aropixel\AdminBundle\Security\Exception\TooOldPasswordAuthenticationException;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Event\AuthenticationTokenCreatedEvent;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Aropixel\AdminBundle\Security\Passport\Badge\UpdateOldPasswordBadge;
use TickLive\ShopBundle\Infrastructure\Stack\Symfony\Authentication\Captcha\CaptchaCredentials;
use TickLive\ShopBundle\Infrastructure\Stack\Symfony\Authentication\Captcha\CaptchaTrigger;
use TickLive\ShopBundle\Infrastructure\Stack\Symfony\Authentication\Provider\UserProvider;
use TickLive\ShopBundle\Infrastructure\Stack\Symfony\Authentication\RateLimiter\LocalLoginRateLimiter;
use TickLive\ShopBundle\Infrastructure\Stack\Symfony\Authentication\Route\SkippedRouteProvider;
use TickLive\ShopBundle\Services\Ticketing\ShopRouterInterface;


class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    protected CaptchaTrigger $captchaTrigger;
    protected ChannelContextInterface $channelContext;
    protected LocalLoginRateLimiter $limiter;
    protected ShopRouterInterface $shopRouter;
    protected SkippedRouteProvider $skippedRouteProvider;
    protected UrlGeneratorInterface $urlGenerator;
    protected UserProvider $userProvider;
    protected $csrfTokenManager;
    protected $parameterBag;
    protected $entityManager;
    protected $passwordEncoder;


    public function __construct(
        CaptchaTrigger $captchaTrigger,
        ChannelContextInterface $channelContext,
        LocalLoginRateLimiter $limiter,
        ShopRouterInterface $shopRouter,
        SkippedRouteProvider $skippedRouteProvider,
        UrlGeneratorInterface $urlGenerator,
        UserProvider $userProvider,
        CsrfTokenManagerInterface $csrfTokenManager,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        $this->captchaTrigger = $captchaTrigger;
        $this->channelContext = $channelContext;
        $this->limiter = $limiter;
        $this->shopRouter = $shopRouter;
        $this->skippedRouteProvider = $skippedRouteProvider;
        $this->userProvider = $userProvider;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->parameterBag = $parameterBag;
        $this->passwordEncoder = $passwordEncoder;
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

        $user = $this->getUser($credentials, $this->userProvider);

        $passport = new Passport(
            new UserBadge($credentials['email'], [$this->userProvider, 'loadUserByIdentifier']),
            new PasswordCredentials($credentials['password']),
            [new RememberMeBadge(), new CaptchaCredentials($credentials['g-recaptcha-response'])]
        );

        $passport->addBadge(new UpdateOldPasswordBadge($user));

        // Ajout d'un badge de gestion du Csrf
        $passport->addBadge(new CsrfTokenBadge('authenticate', $credentials['csrf_token']));

        if ($this->userProvider instanceof PasswordUpgraderInterface) {
            $passport->addBadge(new PasswordUpgradeBadge($credentials['password'], $this->userProvider));
        }

        return $passport;
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
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        // For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
        return new RedirectResponse($this->urlGenerator->generate('_admin'));
//        throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        //dd($request,$exception);
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }

        //dd($exception);
        if ($exception instanceof TooOldPasswordAuthenticationException) {
            dd($exception->getUser()->getId());
            return new RedirectResponse($this->urlGenerator->generate('aropixel_admin_reset_rgpd_password', ['user' => $exception->getUser()->getId()]));
        }

        $url = $this->getLoginUrl($request);

        return new RedirectResponse($url);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('aropixel_admin_security_login');
    }

}

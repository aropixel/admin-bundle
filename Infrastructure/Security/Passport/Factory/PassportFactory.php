<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Passport\Factory;

use Aropixel\AdminBundle\Domain\Entity\User;
use Aropixel\AdminBundle\Infrastructure\Security\Authentication\Credentials\CredentialsResolverInterface;
use Aropixel\AdminBundle\Infrastructure\Security\Authentication\User\Provider\AdminUserProviderInterface;
use Aropixel\AdminBundle\Infrastructure\Security\Passport\Badge\TooOldLastLoginBadge;
use Aropixel\AdminBundle\Infrastructure\Security\Passport\Badge\TooOldPasswordBadge;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class PassportFactory implements PassportFactoryInterface
{
    private CredentialsResolverInterface $credentialsResolver;
    private ParameterBagInterface $parameterBag;
    private AdminUserProviderInterface $userProvider;

    /**
     * @param CredentialsResolverInterface $credentialsResolver
     * @param ParameterBagInterface $parameterBag
     * @param AdminUserProviderInterface $userProvider
     */
    public function __construct(CredentialsResolverInterface $credentialsResolver, ParameterBagInterface $parameterBag, AdminUserProviderInterface $userProvider)
    {
        $this->credentialsResolver = $credentialsResolver;
        $this->parameterBag = $parameterBag;
        $this->userProvider = $userProvider;
    }


    public function createPassport(Request $request) : Passport
    {
        $credentials = $this->credentialsResolver->getCredentials($request);
        $userBadge = new UserBadge($credentials['email'], [$this->userProvider, 'loadUserByIdentifier']);
        $passwordBadge = new PasswordCredentials($credentials['password']);

        $passport = new Passport(
            $userBadge,
            $passwordBadge,
            [new RememberMeBadge()]
        );

        /** @var \Aropixel\AdminBundle\Domain\Entity\User $user */
        $user = $userBadge->getUser();
        $passport->addBadge(new TooOldPasswordBadge($user, $this->parameterBag->get('passwordPeriod')));
        $passport->addBadge(new TooOldLastLoginBadge($user));

        // Add Csrf token
        $passport->addBadge(new CsrfTokenBadge('authenticate', $credentials['csrf_token']));

        if ($this->userProvider instanceof PasswordUpgraderInterface) {
            $passport->addBadge(new PasswordUpgradeBadge($credentials['password'], $this->userProvider));
        }

        return $passport;
    }
}

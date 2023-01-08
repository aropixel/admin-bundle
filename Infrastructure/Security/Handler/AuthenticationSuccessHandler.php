<?php

namespace Aropixel\AdminBundle\Infrastructure\Security\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private EntityManagerInterface $em;
    private RouterInterface $router;

    use TargetPathTrait;


    /**
     * @param EntityManagerInterface $em
     * @param RouterInterface $router
     */
    public function __construct(EntityManagerInterface $em, RouterInterface $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    public function handleSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        $user = $token->getUser();
        $user->setPasswordAttempts(0);
        $user->setLastLogin(new \DateTime('now'));
        $this->em->flush();

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('_admin'));

    }
}
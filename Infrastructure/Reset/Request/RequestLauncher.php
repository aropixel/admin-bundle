<?php

namespace Aropixel\AdminBundle\Infrastructure\Reset\Request;

use Aropixel\AdminBundle\Domain\Reset\Email\ResetEmailSenderInterface;
use Aropixel\AdminBundle\Domain\Reset\Request\RequestLauncherInterface;
use Aropixel\AdminBundle\Domain\Reset\Request\ResetLinkFactoryInterface;
use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Infrastructure\User\UserRepositoryProvider;
use Aropixel\AdminBundle\Security\UniqueTokenGenerator;
use Doctrine\ORM\EntityManagerInterface;


class RequestLauncher implements RequestLauncherInterface
{
    private EntityManagerInterface $em;
    private ResetLinkFactoryInterface $resetLinkFactory;
    private ResetEmailSenderInterface $resetEmailSender;
    private UniqueTokenGenerator $uniqueTokenGenerator;


    /**
     * @param EntityManagerInterface $em
     * @param ResetLinkFactoryInterface $resetLinkFactory
     * @param ResetEmailSenderInterface $resetEmailSender
     * @param UniqueTokenGenerator $uniqueTokenGenerator
     */
    public function __construct(EntityManagerInterface $em, ResetLinkFactoryInterface $resetLinkFactory, ResetEmailSenderInterface $resetEmailSender, UniqueTokenGenerator $uniqueTokenGenerator)
    {
        $this->em = $em;
        $this->resetLinkFactory = $resetLinkFactory;
        $this->resetEmailSender = $resetEmailSender;
        $this->uniqueTokenGenerator = $uniqueTokenGenerator;
    }


    public function reset(User $user)
    {
        $user->setPasswordResetToken($this->uniqueTokenGenerator->generate());
        $user->setPasswordRequestedAt(new \DateTime());
        $this->em->flush();

        $this->resetEmailSender->sendResetEmail($user, $this->resetLinkFactory->createResetLink($user));
    }

    public function cancelRequest(User $user)
    {
        $user->setPasswordResetToken(null);
        $user->setPasswordRequestedAt(null);
        $this->em->flush();
    }


}
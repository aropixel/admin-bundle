<?php

namespace Aropixel\AdminBundle\Infrastructure\Reset\Request;

use Aropixel\AdminBundle\Domain\Reset\Email\ResetEmailSenderInterface;
use Aropixel\AdminBundle\Domain\Reset\Request\RequestLauncherInterface;
use Aropixel\AdminBundle\Domain\Reset\Request\ResetLinkFactoryInterface;
use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Infrastructure\Reset\Token\UniqueTokenGenerator;
use Doctrine\ORM\EntityManagerInterface;

class RequestLauncher implements RequestLauncherInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ResetLinkFactoryInterface $resetLinkFactory,
        private readonly ResetEmailSenderInterface $resetEmailSender,
        private readonly UniqueTokenGenerator $uniqueTokenGenerator
    ) {
    }

    public function reset(UserInterface $user)
    {
        $user->setPasswordResetToken($this->uniqueTokenGenerator->generate());
        $user->setPasswordRequestedAt(new \DateTime());
        $this->em->flush();

        $this->resetEmailSender->sendResetEmail($user, $this->resetLinkFactory->createResetLink($user));
    }

    public function cancelRequest(UserInterface $user)
    {
        $user->setPasswordResetToken(null);
        $user->setPasswordRequestedAt(null);
        $this->em->flush();
    }
}

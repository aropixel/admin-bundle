<?php

namespace Aropixel\AdminBundle\Component\Reset\Request;

use Aropixel\AdminBundle\Component\Reset\Email\ResetEmailSenderInterface;
use Aropixel\AdminBundle\Component\Reset\Token\UniqueTokenGenerator;
use Aropixel\AdminBundle\Entity\UserInterface;
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

    public function reset(UserInterface $user): void
    {
        $user->setPasswordResetToken($this->uniqueTokenGenerator->generate());
        $user->setPasswordRequestedAt(new \DateTime());
        $this->em->flush();

        $this->resetEmailSender->sendResetEmail($user, $this->resetLinkFactory->createResetLink($user));
    }

    public function cancelRequest(UserInterface $user): void
    {
        $user->setPasswordResetToken(null);
        $user->setPasswordRequestedAt(null);
        $this->em->flush();
    }
}

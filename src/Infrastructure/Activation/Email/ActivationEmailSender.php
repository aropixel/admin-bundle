<?php

namespace Aropixel\AdminBundle\Infrastructure\Activation\Email;

use Aropixel\AdminBundle\Domain\Activation\Email\ActivationEmailSenderInterface;
use Aropixel\AdminBundle\Domain\Activation\Request\ActivationLinkFactoryInterface;
use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Infrastructure\Reset\Token\UniqueTokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;

class ActivationEmailSender implements ActivationEmailSenderInterface
{
    public function __construct(
        private readonly ActivationLinkFactoryInterface $activationLinkFactory,
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
        private readonly ParameterBagInterface $parameterBag,
        private readonly UniqueTokenGenerator $uniqueTokenGenerator
    ) {
    }

    public function sendActivationEmail(UserInterface $user)
    {
        $user->setPasswordResetToken($this->uniqueTokenGenerator->generate());
        $user->setPasswordRequestedAt(new \DateTime());
        $this->em->flush();

        $client = $this->parameterBag->get('aropixel_admin.client');
        $sender = \array_key_exists('email', $client) && $client['email'] ? $client['email'] : $user->getEmail();

        $email = (new TemplatedEmail())
            ->from($sender)
            ->to($user->getEmail())
            ->subject('Activation de votre compte')
            ->htmlTemplate('@AropixelAdmin/Email/activation.html.twig')
            ->context([
                'user' => $user,
                'link' => $this->activationLinkFactory->createActivationLink($user),
            ])
        ;

        $this->mailer->send($email);
    }
}

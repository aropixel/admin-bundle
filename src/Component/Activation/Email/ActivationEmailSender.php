<?php

namespace Aropixel\AdminBundle\Component\Activation\Email;

use Aropixel\AdminBundle\Component\Activation\Request\ActivationLinkFactoryInterface;
use Aropixel\AdminBundle\Component\Reset\Token\UniqueTokenGenerator;
use Aropixel\AdminBundle\Entity\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;

class ActivationEmailSender implements ActivationEmailSenderInterface
{
    public function __construct(
        private readonly ActivationLinkFactoryInterface $activationLinkFactory,
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
        private readonly ParameterBagInterface $parameterBag,
        private readonly UniqueTokenGenerator $uniqueTokenGenerator,
        private readonly KernelInterface $kernel,
    ) {
    }

    public function sendActivationEmail(UserInterface $user): void
    {
        $user->setPasswordResetToken($this->uniqueTokenGenerator->generate());
        $user->setPasswordRequestedAt(new \DateTime());
        $this->em->flush();

        $client = $this->parameterBag->get('aropixel_admin.client');
        $theme = $this->parameterBag->get('aropixel_admin.theme');
        $sender = \array_key_exists('email', $client) && $client['email'] ? $client['email'] : $user->getEmail();
        $logoPath = $theme['logo']['path'] ?? null;

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

        $email->embedFromPath($this->kernel->getProjectDir() . '/public/' . $logoPath, 'logo');

        $this->mailer->send($email);
    }
}

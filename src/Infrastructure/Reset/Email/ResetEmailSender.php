<?php

namespace Aropixel\AdminBundle\Infrastructure\Reset\Email;

use Aropixel\AdminBundle\Domain\Reset\Email\ResetEmailSenderInterface;
use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;

class ResetEmailSender implements ResetEmailSenderInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ParameterBagInterface $parameterBag,
        private readonly KernelInterface $kernel,
    ) {
    }

    public function sendResetEmail(UserInterface $user, string $resetLink): void
    {
        $client = $this->parameterBag->get('aropixel_admin.client');
        $theme = $this->parameterBag->get('aropixel_admin.theme');
        $sender = \array_key_exists('email', $client) && $client['email'] ? $client['email'] : $user->getEmail();
        $logoPath = \array_key_exists('logo_path', $theme) && $theme['logo_path'] ? $theme['logo_path'] : null;

        $email = (new TemplatedEmail())
            ->from($sender)
            ->to($user->getEmail())
            ->subject('Modification de votre mot de passe')
            ->htmlTemplate('@AropixelAdmin/Email/reset.html.twig')
            ->context([
                'user' => $user,
                'link' => $resetLink,
            ])
        ;

        $email->embedFromPath($this->kernel->getProjectDir() . '/public/' . $logoPath, 'logo');

        $this->mailer->send($email);
    }
}

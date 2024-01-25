<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 04/05/2020 à 15:04
 */

namespace Aropixel\AdminBundle\Infrastructure\Reset\Email;

use Aropixel\AdminBundle\Domain\Reset\Email\ResetEmailSenderInterface;
use Aropixel\AdminBundle\Entity\UserInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;


class ResetEmailSender implements ResetEmailSenderInterface
{
    private MailerInterface $mailer;
    private ParameterBagInterface $parameterBag;

    /**
     * @param MailerInterface $mailer
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(MailerInterface $mailer, ParameterBagInterface $parameterBag)
    {
        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
    }


    public function sendResetEmail(UserInterface $user, string $resetLink)
    {
        $client = $this->parameterBag->get('aropixel_admin.client');
        $sender = array_key_exists('email', $client) && $client['email'] ? $client['email'] : $user->getEmail();

        $email = (new TemplatedEmail())
            ->from($sender)
            ->to($user->getEmail())
            ->subject('Modification de votre mot de passe')
            ->htmlTemplate('@AropixelAdmin/Email/reset.html.twig')
            ->context([
                'user' => $user,
                'link' => $resetLink
            ])
        ;

        $this->mailer->send($email);
    }

}

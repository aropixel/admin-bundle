<?php


namespace Aropixel\AdminBundle\Infrastructure\Activation\Email;

use Aropixel\AdminBundle\Domain\Activation\Email\ActivationEmailSenderInterface;
use Aropixel\AdminBundle\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;


class ActivationEmailSender implements ActivationEmailSenderInterface
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


    public function sendActivationEmail(User $user, string $creationLink)
    {
        $client = $this->parameterBag->get('aropixel_admin.client');
        $sender = array_key_exists('email', $client) && $client['email'] ? $client['email'] : $user->getEmail();

        $email = (new TemplatedEmail())
            ->from($sender)
            ->to($user->getEmail())
            ->subject('Activation de votre compte')
            ->htmlTemplate('@AropixelAdmin/Email/activation.html.twig')
            ->context([
                'user' => $user,
                'link' => $creationLink
            ])
        ;

        $this->mailer->send($email);
    }

}

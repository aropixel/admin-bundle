<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 04/05/2020 à 15:04
 */

namespace Aropixel\AdminBundle\Email;

use Aropixel\AdminBundle\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;


class ResetEmailSender
{
    /** @var MailerInterface */
    private $mailer;

    /** @var ParameterBagInterface */
    private $parameterBag;

    /** @var RouterInterface */
    private $router;


    /**
     * ResetEmailSender constructor.
     * @param MailerInterface $mailer
     * @param ParameterBagInterface $parameterBag
     * @param RouterInterface $router
     */
    public function __construct(MailerInterface $mailer, ParameterBagInterface $parameterBag, RouterInterface $router)
    {
        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
        $this->router = $router;
    }


    public function sendResetEmail(User $user)
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
                'link' => $this->router->generate('aropixel_admin_reset_password', ['token' => $user->getPasswordResetToken()], UrlGeneratorInterface::ABSOLUTE_URL)
            ])
        ;

        $this->mailer->send($email);
    }

}

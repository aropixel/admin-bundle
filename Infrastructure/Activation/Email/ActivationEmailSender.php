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
    private ActivationLinkFactoryInterface $activationLinkFactory;
    private EntityManagerInterface $em;
    private MailerInterface $mailer;
    private ParameterBagInterface $parameterBag;
    private UniqueTokenGenerator $uniqueTokenGenerator;


    /**
     * @param ActivationLinkFactoryInterface $activationLinkFactory
     * @param EntityManagerInterface $em
     * @param MailerInterface $mailer
     * @param ParameterBagInterface $parameterBag
     * @param UniqueTokenGenerator $uniqueTokenGenerator
     */
    public function __construct(ActivationLinkFactoryInterface $activationLinkFactory, EntityManagerInterface $em, MailerInterface $mailer, ParameterBagInterface $parameterBag, UniqueTokenGenerator $uniqueTokenGenerator)
    {
        $this->activationLinkFactory = $activationLinkFactory;
        $this->em = $em;
        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
        $this->uniqueTokenGenerator = $uniqueTokenGenerator;
    }


    public function sendActivationEmail(UserInterface $user)
    {
        $user->setPasswordResetToken($this->uniqueTokenGenerator->generate());
        $user->setPasswordRequestedAt(new \DateTime());
        $this->em->flush();


        $client = $this->parameterBag->get('aropixel_admin.client');
        $sender = array_key_exists('email', $client) && $client['email'] ? $client['email'] : $user->getEmail();

        $email = (new TemplatedEmail())
            ->from($sender)
            ->to($user->getEmail())
            ->subject('Activation de votre compte')
            ->htmlTemplate('@AropixelAdmin/Email/activation.html.twig')
            ->context([
                'user' => $user,
                'link' => $this->activationLinkFactory->createActivationLink($user)
            ])
        ;

        $this->mailer->send($email);
    }

}

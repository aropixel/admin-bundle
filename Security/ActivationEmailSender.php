<?php


namespace Aropixel\AdminBundle\Security;

use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Security\UniqueTokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;


class ActivationEmailSender implements ActivationEmailSenderInterface
{
    /** @var ActivationLinkFactoryInterface  */
    private $activationLinkFactory;

    /** @var EntityManagerInterface  */
    private $em;

    /** @var MailerInterface  */
    private $mailer;

    /** @var ParameterBagInterface  */
    private $parameterBag;

    /** @var UniqueTokenGenerator  */
    private $uniqueTokenGenerator;


    /**
     * @param ActivationLinkFactoryInterface $activationLinkFactory
     * @param EntityManagerInterface $em
     * @param MailerInterface $mailer
     * @param ParameterBagInterface $parameterBag
     * @param UniqueTokenGenerator $generator
     */
    public function __construct(ActivationLinkFactoryInterface $activationLinkFactory, EntityManagerInterface $em, MailerInterface $mailer, ParameterBagInterface $parameterBag, UniqueTokenGenerator $uniqueTokenGenerator)
    {
        $this->activationLinkFactory = $activationLinkFactory;
        $this->em = $em;
        $this->mailer = $mailer;
        $this->parameterBag = $parameterBag;
        $this->uniqueTokenGenerator = $uniqueTokenGenerator;
    }


    public function sendActivationEmail(User $user)
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

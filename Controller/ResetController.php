<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 04/05/2020 à 11:31
 */

namespace Aropixel\AdminBundle\Controller;

use Aropixel\AdminBundle\Domain\Reset\Email\ResetEmailSenderInterface;
use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Form\Reset\RequestType;
use Aropixel\AdminBundle\Form\Reset\ResetPasswordType;
use Aropixel\AdminBundle\Security\UniqueTokenGenerator;
use Aropixel\AdminBundle\Security\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class ResetController extends AbstractController
{

    /** @var ParameterBagInterface */
    private $parameterBag;

    /** @var string */
    private $model;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var UniqueTokenGenerator */
    private $generator;

    /** @var ResetEmailSenderInterface */
    private $resetEmailSender;


    public function __construct(ParameterBagInterface $parameterBag, UserManager $userManager, EntityManagerInterface $entityManager, UniqueTokenGenerator $generator, ResetEmailSenderInterface $resetEmailSender, UserPasswordHasherInterface $passwordHasher)
    {
        $this->parameterBag = $parameterBag;
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->generator = $generator;
        $this->resetEmailSender = $resetEmailSender;
        $this->passwordHasher = $passwordHasher;

        $entities = $this->parameterBag->get('aropixel_admin.entities');
        $this->model = $entities[UserInterface::class];

    }




    public function resetTooOldPassword(int $userId): Response
    {
        $this->resetPasswordAfterFail($userId);

        return $this->redirectToRoute('aropixel_admin_too_old_password_reset_request_info');
    }

    public function resetTooOldLastLogin(int $userId): Response
    {
        $this->resetPasswordAfterFail($userId);

        return $this->redirectToRoute('aropixel_admin_too_old_last_login_reset_request_info');
    }

    public function resetBlockedLogin(int $userId): Response
    {
        $this->resetPasswordAfterFail($userId);

        return $this->redirectToRoute('aropixel_admin_blocked_reset_request_info');
    }


    protected function resetPasswordAfterFail(int $userId)
    {
        $user = $this->entityManager->getRepository($this->model)->find($userId);

        if (null === $user) {
            throw new NotFoundHttpException("Cet utilisateur n'existe pas.");
        }

        $user->setPasswordResetToken($this->generator->generate());
        $user->setPasswordRequestedAt(new \DateTime());

        $this->entityManager->flush();

        $this->resetEmailSender->sendResetEmail($user, 1);
    }

}

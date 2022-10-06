<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 04/05/2020 à 11:31
 */

namespace Aropixel\AdminBundle\Controller;

use Aropixel\AdminBundle\Email\ResetEmailSender;
use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Form\Reset\RequestType;
use Aropixel\AdminBundle\Form\Reset\ResetPasswordType;
use Aropixel\AdminBundle\Security\UniqueTokenGenerator;
use Aropixel\AdminBundle\Security\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class ResetController extends AbstractController
{

    /** @var ParameterBagInterface */
    private $parameterBag;

    /** @var UserManager */
    private $userManager;

    /** @var string */
    private $model;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var UniqueTokenGenerator */
    private $generator;

    /** @var ResetEmailSender */
    private $resetEmailSender;


    public function __construct(ParameterBagInterface $parameterBag, UserManager $userManager, EntityManagerInterface $entityManager, UniqueTokenGenerator $generator, ResetEmailSender $resetEmailSender)
    {
        $this->parameterBag = $parameterBag;
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->generator = $generator;
        $this->resetEmailSender = $resetEmailSender;

        $entities = $this->parameterBag->get('aropixel_admin.entities');
        $this->model = $entities[UserInterface::class];

    }


    public function requestReset(Request $request, UniqueTokenGenerator $generator, ResetEmailSender $resetEmailSender)
    {
        $form = $this->createForm(RequestType::class);

        $notFound = false;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $email = $form->get('email')->getData();
            $user = $this->entityManager->getRepository($this->model)->findOneBy(['email' => $email]);

            if ($user) {
                $user->setPasswordResetToken($generator->generate());
                $user->setPasswordRequestedAt(new \DateTime());
                $this->entityManager->flush();

                $resetEmailSender->sendResetEmail($user);

                return $this->redirectToRoute('aropixel_admin_reset_request_info');
            }
            else {
                $notFound = true;
            }

        }

        return $this->render('@AropixelAdmin/Reset/request.html.twig', ['form' => $form->createView(), 'not_found' => $notFound]);
    }


    public function requestResetInfo()
    {
        return $this->render('@AropixelAdmin/Reset/request_info.html.twig');
    }


    public function resetPassword(Request $request, string $token): Response
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository($this->model)->findOneBy(['passwordResetToken' => $token]);

        if (null === $user) {
            throw new NotFoundHttpException('Token not found.');
        }

        $lifetime = new \DateInterval('P1D');
        if (!$user->isPasswordRequestNonExpired($lifetime)) {
            $this->handleExpiredToken($user);
            return $this->redirectToRoute('aropixel_admin_reset_result');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $form->get('password')->getViewData();
            $this->entityManager->flush();
            $this->handleResetPassword($user, $password['first']);

            return $this->redirectToRoute('aropixel_admin_reset_result');
        }

        return $this->render('@AropixelAdmin/Reset/reset.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }

    public function resetSuccess()
    {
        return $this->render('@AropixelAdmin/Reset/reset_result.html.twig');
    }

    protected function handleExpiredToken(UserInterface $user)
    {
        $user->setPasswordResetToken(null);
        $user->setPasswordRequestedAt(null);

        $this->entityManager->flush();
    }

    protected function handleResetPassword(UserInterface $user, string $newPassword)
    {
        $user->setPlainPassword($newPassword);
        $user->setPasswordResetToken(null);
        $user->setPasswordRequestedAt(null);
        $user->setLastLogin(null);
        $user->setBlocked(0);

        $this->userManager->updateUser($user, true);
    }


    public function resetTooOldPassword(int $userId): Response
    {
        $this->resetPasswordAfterFail($userId, 0);

        return $this->redirectToRoute('aropixel_admin_too_old_password_reset_request_info');
    }

    public function resetTooOldLastLogin(UniqueTokenGenerator $generator, ResetEmailSender $resetEmailSender, int $userId): Response
    {
        $this->resetPasswordAfterFail($userId, 0);

        return $this->redirectToRoute('aropixel_admin_too_old_last_login_reset_request_info');
    }

    public function resetBlockedLogin(int $userId): Response
    {
        $this->resetPasswordAfterFail($userId, 1);

        return $this->redirectToRoute('aropixel_admin_blocked_reset_request_info');
    }

    public function tooOldPasswordResetRequestInfo()
    {
        return $this->render('@AropixelAdmin/Reset/too_old_password_request_info.html.twig');
    }

    public function tooOldLastLoginResetRequestInfo()
    {
        return $this->render('@AropixelAdmin/Reset/too_old_last_login_request_info.html.twig');
    }

    public function blockedResetRequestInfo()
    {
        return $this->render('@AropixelAdmin/Reset/blocked_request_info.html.twig');
    }

    protected function resetPasswordAfterFail(int $userId, bool $blocked)
    {
        $user = $this->entityManager->getRepository($this->model)->find($userId);

        if (null === $user) {
            throw new NotFoundHttpException("Cet utilisateur n'existe pas.");
        }

        $user->setPasswordResetToken($this->generator->generate());
        $user->setPasswordRequestedAt(new \DateTime());
        $user->setBlocked($blocked);

        $this->entityManager->flush();

        $this->resetEmailSender->sendResetEmail($user);
    }

}

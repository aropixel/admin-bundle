<?php

namespace Aropixel\AdminBundle\Controller\Reset;

use Aropixel\AdminBundle\Component\Reset\PasswordResetHandlerInterface;
use Aropixel\AdminBundle\Component\Reset\Request\RequestLauncherInterface;
use Aropixel\AdminBundle\Component\User\Exception\UnchangedPasswordException;
use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Form\Security\Reset\ResetPasswordType;
use Aropixel\AdminBundle\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResetPasswordAction extends AbstractController
{
    public function __construct(
        private readonly PasswordResetHandlerInterface $passwordResetHandler,
        private readonly RequestLauncherInterface $requestLauncher,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(Request $request, string $token): Response
    {
        /** @var ?User $user */
        $user = $this->userRepository->findOneBy(['passwordResetToken' => $token]);
        if (null === $user) {
            throw new NotFoundHttpException('Token not found.');
        }

        // Reset request expire after 1 day
        $lifetime = new \DateInterval('P1D');
        if ($user->isPasswordRequestExpired($lifetime)) {
            $this->requestLauncher->cancelRequest($user);

            return $this->redirectToRoute('aropixel_admin_request_status', ['status' => RequestStatusAction::EXPIRED]);
        }

        $error = null;
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->get('first')->getViewData();

            try {
                $this->passwordResetHandler->update($user, $password);

                return $this->redirectToRoute('aropixel_admin_request_status', ['status' => RequestStatusAction::SUCCESS]);
            } catch (UnchangedPasswordException) {
                $error = 'Veuillez choisir un mot de passe différent du mot de passe actuel.';
            }
        }

        return $this->render('@AropixelAdmin/Reset/reset.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
                'error' => $error,
            ]
        );
    }
}

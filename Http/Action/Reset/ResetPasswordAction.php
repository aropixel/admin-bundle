<?php

namespace Aropixel\AdminBundle\Http\Action\Reset;

use Aropixel\AdminBundle\Domain\Reset\PasswordResetHandlerInterface;
use Aropixel\AdminBundle\Domain\Reset\Request\RequestLauncherInterface;
use Aropixel\AdminBundle\Domain\User\Exception\UnchangedPasswordException;
use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Form\Reset\ResetPasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResetPasswordAction extends AbstractController
{
    private PasswordResetHandlerInterface $passwordResetHandler;
    private RequestLauncherInterface $requestLauncher;
    private UserRepositoryInterface $userRepository;

    /**
     * @param PasswordResetHandlerInterface $passwordResetHandler
     * @param RequestLauncherInterface $requestLauncher
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(PasswordResetHandlerInterface $passwordResetHandler, RequestLauncherInterface $requestLauncher, UserRepositoryInterface $userRepository)
    {
        $this->passwordResetHandler = $passwordResetHandler;
        $this->requestLauncher = $requestLauncher;
        $this->userRepository = $userRepository;
    }


    public function __invoke(Request $request, string $token)
    {
        /** @var User $user */
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
            }
            catch (UnchangedPasswordException $e) {
                $error = "Veuillez choisir un mot de passe différent du mot de passe actuel.";
            }

        }

        return $this->render('@AropixelAdmin/Reset/reset.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
                'error' => $error
            ]
        );
    }

}

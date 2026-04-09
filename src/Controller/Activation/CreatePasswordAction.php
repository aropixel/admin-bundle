<?php

namespace Aropixel\AdminBundle\Controller\Activation;

use Aropixel\AdminBundle\Component\Activation\PasswordCreationHandlerInterface;
use Aropixel\AdminBundle\Component\User\Exception\UnchangedPasswordException;
use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Form\Security\Activation\CreatePasswordType;
use Aropixel\AdminBundle\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreatePasswordAction extends AbstractController
{
    public function __construct(
        private readonly PasswordCreationHandlerInterface $passwordCreationHandler,
        private readonly UserRepositoryInterface $userRepository,
        private readonly ParameterBagInterface $parameterBag,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function __invoke(Request $request, string $token): Response
    {
        /** @var ?UserInterface $user */
        $user = $this->userRepository->findOneBy(['passwordResetToken' => $token]);
        if (null === $user) {
            throw new NotFoundHttpException('Token not found.');
        }

        // Request expire after 1 day or conf duration
        $duration = $this->parameterBag->get('activationRequestLifeTime');
        $lifetime = $duration ? new \DateInterval($duration) : new \DateInterval('P1D');
        if ($user->isPasswordRequestExpired($lifetime)) {
            return $this->redirectToRoute('aropixel_admin_activation_request_status', ['status' => RequestStatusAction::EXPIRED]);
        }

        $error = null;
        $form = $this->createForm(CreatePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->get('first')->getViewData();

            try {
                $this->passwordCreationHandler->create($user, $password);

                return $this->redirectToRoute('aropixel_admin_activation_request_status', ['status' => RequestStatusAction::SUCCESS]);
            } catch (UnchangedPasswordException) {
                $error = $this->translator->trans('reset.flash.different_password');
            }
        }

        return $this->render('@AropixelAdmin/Activation/create_password.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
                'error' => $error,
            ]
        );
    }
}

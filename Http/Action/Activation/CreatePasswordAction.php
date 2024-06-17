<?php

namespace Aropixel\AdminBundle\Http\Action\Activation;

use Aropixel\AdminBundle\Domain\Activation\PasswordCreationHandlerInterface;
use Aropixel\AdminBundle\Domain\User\Exception\UnchangedPasswordException;
use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Http\Form\Activation\CreatePasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CreatePasswordAction extends AbstractController
{
    public function __construct(
        private readonly PasswordCreationHandlerInterface $passwordCreationHandler,
        private readonly UserRepositoryInterface $userRepository,
        private readonly ParameterBagInterface $parameterBag
    ) {
    }

    public function __invoke(Request $request, string $token)
    {
        /** @var UserInterface $user */
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
                $error = 'Veuillez choisir un mot de passe différent du mot de passe actuel.';
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

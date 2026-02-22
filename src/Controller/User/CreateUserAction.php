<?php

namespace Aropixel\AdminBundle\Controller\User;

use Aropixel\AdminBundle\Component\Activation\Email\ActivationEmailSenderInterface;
use Aropixel\AdminBundle\Component\User\UserFactoryInterface;
use Aropixel\AdminBundle\Form\Type\UserType;
use Aropixel\AdminBundle\Repository\UserRepositoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateUserAction extends AbstractController
{
    private string $form = UserType::class;

    public function __construct(
        private readonly ActivationEmailSenderInterface $activationEmailSender,
        private readonly UserFactoryInterface $userFactory,
        private readonly UserRepositoryInterface $userRepository,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->userFactory->createUser();

        $form = $this->createForm($this->form, $user, [
            'new' => true,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $user->getEmail();

            if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', $this->translator->trans('user.flash.invalid_email'));

                return $this->render('@AropixelAdmin/User/Crud/form.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                ]);
            }

            // Vérifie si l'utilisateur n'existe pas déjà
            $exists = $this->userRepository->findUserByEmail($user->getEmail());
            if ($exists) {
                $this->addFlash('error', $this->translator->trans('user.flash.email_used'));

                return $this->render('@AropixelAdmin/User/Crud/form.html.twig', ['user' => $user, 'form' => $form->createView()]);
            }

            $this->userRepository->create($user);
            $this->activationEmailSender->sendActivationEmail($user);

            $this->addFlash('notice', $this->translator->trans('user.flash.created_with_email'));

            return $this->redirectToRoute('aropixel_admin_user_edit', ['id' => $user->getId()]);
        }

        return $this->render('@AropixelAdmin/User/Crud/form.html.twig', ['user' => $user, 'sendButton' => false, 'form' => $form->createView()]);
    }
}

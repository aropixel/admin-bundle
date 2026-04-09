<?php

namespace Aropixel\AdminBundle\Controller\User;

use Aropixel\AdminBundle\Component\Activation\Email\ActivationEmailSenderInterface;
use Aropixel\AdminBundle\Component\User\PasswordInitializer;
use Aropixel\AdminBundle\Form\Type\UserType;
use Aropixel\AdminBundle\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class SendActivationLinkAction extends AbstractController
{
    public function __construct(
        private readonly ActivationEmailSenderInterface $activationEmailSender,
        private readonly PasswordInitializer $passwordInitializer,
        private readonly RequestStack $request,
        private readonly UserRepositoryInterface $userRepository,
        private readonly TranslatorInterface $translator
    ) {
    }

    private string $form = UserType::class;

    public function __invoke(int $id): Response
    {
        $user = $this->userRepository->find($id);

        if (null === $user) {
            throw $this->createNotFoundException();
        }

        $editForm = $this->createForm($this->form, $user);
        $editForm->handleRequest($this->request->getMainRequest());

        if ($this->passwordInitializer->stillPendingPasswordCreation($user)) {
            $this->activationEmailSender->sendActivationEmail($user);
            $this->addFlash('notice', $this->translator->trans('user.flash.activation_sent'));
        } else {
            $this->addFlash('notice', $this->translator->trans('user.flash.activation_already_initialized'));
        }

        return $this->redirectToRoute('aropixel_admin_user_edit', ['id' => $user->getId()]);
    }
}

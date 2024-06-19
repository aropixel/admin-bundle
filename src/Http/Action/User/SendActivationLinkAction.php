<?php

namespace Aropixel\AdminBundle\Http\Action\User;

use Aropixel\AdminBundle\Domain\Activation\Email\ActivationEmailSenderInterface;
use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Form\Type\UserType;
use Aropixel\AdminBundle\Infrastructure\User\PasswordInitializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class SendActivationLinkAction extends AbstractController
{
    public function __construct(
        private readonly ActivationEmailSenderInterface $activationEmailSender,
        private readonly PasswordInitializer $passwordInitializer,
        private readonly RequestStack $request,
        private readonly UserRepositoryInterface $userRepository
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
            $this->addFlash('notice', 'L\'email de création de compte a bien été renvoyé.');
        } else {
            $this->addFlash('notice', 'Impossible de renvoyer le mail de création de compte. Le compte a déjà été initialisé.');
        }

        return $this->redirectToRoute('aropixel_admin_user_edit', ['id' => $user->getId()]);
    }
}

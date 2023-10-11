<?php

namespace Aropixel\AdminBundle\Http\Action\User;

use Aropixel\AdminBundle\Domain\User\PasswordUpdaterInterface;
use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Form\Type\UserType;
use Aropixel\AdminBundle\Repository\UserRepository;
use Aropixel\AdminBundle\Security\ActivationEmailSenderInterface;
use Aropixel\AdminBundle\Security\PasswordInitializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class SendActivationLinkAction extends AbstractController
{
    /** @var ActivationEmailSenderInterface  */
    private $activationEmailSender;

    /** @var PasswordInitializer  */
    private $passwordInitializer;

    /** @var RequestStack  */
    private $request;

    /** @var UserRepository  */
    private $userRepository;

    public function __construct(
        ActivationEmailSenderInterface $activationEmailSender,
        PasswordInitializer $passwordInitializer,
        RequestStack $request,
        UserRepository $userRepository
    )
    {
        $this->activationEmailSender = $activationEmailSender;
        $this->passwordInitializer = $passwordInitializer;
        $this->request = $request;
        $this->userRepository = $userRepository;
    }

    private $form = UserType::class;


    public function __invoke(int $id) : Response
    {
         $user = $this->userRepository->find($id);

        if (is_null($user)) {
            throw $this->createNotFoundException();
        }

        $editForm = $this->createForm($this->form, $user);
        $editForm->handleRequest($this->request->getMasterRequest());

        if ($this->passwordInitializer->stillPendingPasswordCreation($user)) {

            $this->activationEmailSender->sendActivationEmail($user);
            $this->addFlash('notice', 'L\'email de création de compte a bien été renvoyé.');

        }
        else {
            $this->addFlash('notice', 'Impossible de renvoyer le mail de création de compte. Le compte a déjà été initialisé.');
        }

        return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
    }
}

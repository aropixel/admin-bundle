<?php

namespace Aropixel\AdminBundle\Http\Action\User;

use Aropixel\AdminBundle\Domain\Activation\Email\ActivationEmailSenderInterface;
use Aropixel\AdminBundle\Domain\User\UserFactoryInterface;
use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Form\Type\UserType;
use Aropixel\AdminBundle\Infrastructure\User\PasswordInitializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateUserAction extends AbstractController
{

    private ActivationEmailSenderInterface $activationEmailSender;
    private UserFactoryInterface $userFactory;
    private UserRepositoryInterface $userRepository;
    private string $form = UserType::class;


    /**
     * @param ActivationEmailSenderInterface $activationEmailSender
     * @param UserFactoryInterface $userFactory
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(ActivationEmailSenderInterface $activationEmailSender, UserFactoryInterface $userFactory, UserRepositoryInterface $userRepository)
    {
        $this->activationEmailSender = $activationEmailSender;
        $this->userFactory = $userFactory;
        $this->userRepository = $userRepository;
    }


    public function __invoke(Request $request) : Response
    {
        $user = $this->userFactory->createUser();

        $form = $this->createForm($this->form, $user, [
            'new' => true,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Vérifie si l'utilisateur n'existe pas déjà
            $exists = $this->userRepository->findUserByEmail($user->getEmail());
            if ($exists) {
                $this->addFlash('error','Cet email est déjà utilisé pour un utilisateur.');
                return $this->render('@AropixelAdmin/User/Crud/form.html.twig', array(
                    'user' => $user,
                    'form' => $form->createView(),
                ));
            }

            $this->userRepository->create($user);
            $this->activationEmailSender->sendActivationEmail($user);


            $this->addFlash('notice', 'Votre utilisateur a bien été enregistré. Un email lui a été envoyé pour qu\'il puisse finaliser l\'ouverture de son compte.');
            return $this->redirectToRoute('aropixel_admin_user_edit', array('id' => $user->getId()));
        }

        return $this->render('@AropixelAdmin/User/Crud/form.html.twig', array(
            'user' => $user,
            'sendButton' => false,
            'form' => $form->createView(),
        ));
    }
}
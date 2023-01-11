<?php

namespace Aropixel\AdminBundle\Http\Action\User;

use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Form\Type\UserType;
use Aropixel\AdminBundle\Security\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class CreateUserAction extends AbstractController
{
    public function __construct(
        private readonly RequestStack $request,
        private readonly UserManager $userManager,
    ){}

    private string $model = User::class;
    private string $form = UserType::class;

    public function __invoke() : Response
    {
        $user = new $this->model();

        $form = $this->createForm($this->form, $user, [
            'new' => true,
        ]);

        $form->handleRequest($this->request->getMainRequest());
        if ($form->isSubmitted() && $form->isValid()) {

            // Vérifie si l'utilisateur n'existe pas déjà
            $exists = $this->userManager->findUserByEmail($user->getEmail());
            if ($exists) {
                $this->addFlash('error','Cet email est déjà utilisé pour un utilisateur.');
                return $this->render('@AropixelAdmin/User/Crud/form.html.twig', array(
                    'user' => $user,
                    'form' => $form->createView(),
                ));
            }

            $this->userManager->updateUser($user);
            $this->addFlash('notice', 'Votre utilisateur a bien été enregistré.');

            return $this->redirectToRoute('aropixel_admin_user_edit', array('id' => $user->getId()));
        }

        return $this->render('@AropixelAdmin/User/Crud/form.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }
}
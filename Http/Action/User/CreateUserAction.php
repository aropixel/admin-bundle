<?php

namespace Aropixel\AdminBundle\Http\Action\User;

use Aropixel\AdminBundle\Domain\User\PasswordUpdaterInterface;
use Aropixel\AdminBundle\Domain\User\UserFactoryInterface;
use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Form\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateUserAction extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PasswordUpdaterInterface $passwordUpdater,
        private readonly UserFactoryInterface $userFactory,
        private readonly UserRepositoryInterface $userRepository
    ){}

    private string $form = UserType::class;

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
                return $this->render('@AropixelAdmin/User/Crud/form.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                ]);
            }

            $this->passwordUpdater->hashPlainPassword($user);
            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('notice', 'Votre utilisateur a bien été enregistré.');
            return $this->redirectToRoute('aropixel_admin_user_edit', ['id' => $user->getId()]);
        }

        return $this->render('@AropixelAdmin/User/Crud/form.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}

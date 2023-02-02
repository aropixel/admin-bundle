<?php

namespace Aropixel\AdminBundle\Http\Action\User;

use Aropixel\AdminBundle\Domain\User\PasswordUpdaterInterface;
use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Form\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateUserAction extends AbstractController
{

    private EntityManagerInterface $em;
    private PasswordUpdaterInterface $passwordUpdater;
    private UserRepositoryInterface $userRepository;


    private string $model = User::class;
    private string $form = UserType::class;


    /**
     * @param EntityManagerInterface $em
     * @param PasswordUpdaterInterface $passwordUpdater
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(EntityManagerInterface $em, PasswordUpdaterInterface $passwordUpdater, UserRepositoryInterface $userRepository)
    {
        $this->em = $em;
        $this->passwordUpdater = $passwordUpdater;
        $this->userRepository = $userRepository;
    }


    public function __invoke(Request $request) : Response
    {
        $user = new $this->model();

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

            $this->passwordUpdater->hashPlainPassword($user);
            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('notice', 'Votre utilisateur a bien été enregistré.');
            return $this->redirectToRoute('aropixel_admin_user_edit', array('id' => $user->getId()));
        }

        return $this->render('@AropixelAdmin/User/Crud/form.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }
}

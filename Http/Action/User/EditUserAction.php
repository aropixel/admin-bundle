<?php

namespace Aropixel\AdminBundle\Http\Action\User;

use Aropixel\AdminBundle\Domain\User\PasswordUpdaterInterface;
use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Form\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class EditUserAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PasswordUpdaterInterface $passwordUpdater,
        private readonly RequestStack $request,
        private readonly UserRepositoryInterface $userRepository
    ){}

    private string $form = UserType::class;


    public function __invoke(int $id) : Response
    {
         $user = $this->userRepository->find($id);

        if (is_null($user)) {
            throw $this->createNotFoundException();
        }

        $editForm = $this->createForm($this->form, $user);
        $editForm->handleRequest($this->request->getMainRequest());

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $this->passwordUpdater->hashPlainPassword($user);
            $this->em->flush();
            $this->addFlash('notice', 'Votre utilisateur a bien été enregistré.');

            return $this->redirectToRoute('aropixel_admin_user_edit', ['id' => $user->getId()]);
        }

        return $this->render('@AropixelAdmin/User/Crud/form.html.twig', [
            'user'   => $user,
            'form'   => $editForm->createView()
        ]);
    }
}

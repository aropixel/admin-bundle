<?php

namespace Aropixel\AdminBundle\Http\Action\User;

use Aropixel\AdminBundle\Domain\User\PasswordUpdaterInterface;
use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Form\Type\UserType;
use Aropixel\AdminBundle\Http\Form\User\FormFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class EditUserAction extends AbstractController
{
    private EntityManagerInterface $em;
    private FormFactory $formFactory;
    private PasswordUpdaterInterface $passwordUpdater;
    private RequestStack $request;
    private UserRepositoryInterface $userRepository;


    private string $form = UserType::class;


    /**
     * @param EntityManagerInterface $em
     * @param FormFactory $formFactory
     * @param PasswordUpdaterInterface $passwordUpdater
     * @param RequestStack $request
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(EntityManagerInterface $em, FormFactory $formFactory, PasswordUpdaterInterface $passwordUpdater, RequestStack $request, UserRepositoryInterface $userRepository)
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->passwordUpdater = $passwordUpdater;
        $this->request = $request;
        $this->userRepository = $userRepository;
    }


    public function __invoke(int $id) : Response
    {
         $user = $this->userRepository->find($id);

        if (is_null($user)) {
            throw $this->createNotFoundException();
        }

        $deleteForm = $this->formFactory->createDeleteForm($user);
        $editForm = $this->createForm($this->form, $user);
        $editForm->handleRequest($this->request->getMainRequest());

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $this->passwordUpdater->hashPlainPassword($user);
            $this->em->flush();
            $this->addFlash('notice', 'Votre utilisateur a bien été enregistré.');

            return $this->redirectToRoute('aropixel_admin_user_edit', array('id' => $user->getId()));
        }

        return $this->render('@AropixelAdmin/User/Crud/form.html.twig', array(
            'user'   => $user,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
}

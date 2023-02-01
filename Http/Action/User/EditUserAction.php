<?php

namespace Aropixel\AdminBundle\Http\Action\User;

use Aropixel\AdminBundle\Form\Type\UserType;
use Aropixel\AdminBundle\Http\Form\User\FormFactory;
use Aropixel\AdminBundle\Repository\UserRepository;
use Aropixel\AdminBundle\Security\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditUserAction extends AbstractController
{
    public function __construct(
        private readonly FormFactory $formFactory,
        private readonly RequestStack $request,
        private readonly TranslatorInterface $translator,
        private readonly UserManager $userManager,
        private readonly UserRepository $userRepository,
    ){}

    private string $form = UserType::class;

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
            $this->userManager->updateUser($user);
            $this->addFlash('notice', $this->translator->trans('Your user has been successfully registered.'));

            return $this->redirectToRoute('aropixel_admin_user_edit', array('id' => $user->getId()));
        }

        return $this->render('@AropixelAdmin/User/Crud/form.html.twig', array(
            'user'   => $user,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
}
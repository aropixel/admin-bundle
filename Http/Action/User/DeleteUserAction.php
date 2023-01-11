<?php

namespace Aropixel\AdminBundle\Http\Action\User;

use Aropixel\AdminBundle\Http\Form\User\FormFactory;
use Aropixel\AdminBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class DeleteUserAction extends AbstractController
{
    public function __construct(
        private readonly FormFactory $formFactory,
        private readonly RequestStack $request,
        private readonly UserRepository $userRepository,
    )
    {}
    public function __invoke(int $id) : Response
    {
        $user = $this->userRepository->find($id);
        if (is_null($user)) {
            throw $this->createNotFoundException();
        }

        $form = $this->formFactory->createDeleteForm($user);
        $form->handleRequest($this->request->getMainRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('notice', 'Votre utilisateur a bien été supprimé.');
            $this->userRepository->remove($user, true);
        }

        return $this->redirect($this->generateUrl('aropixel_admin_user_index'));
    }
}
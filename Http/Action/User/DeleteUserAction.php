<?php

namespace Aropixel\AdminBundle\Http\Action\User;

use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Http\Form\User\FormFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class DeleteUserAction extends AbstractController
{
    private FormFactory $formFactory;
    private RequestStack $request;
    private UserRepositoryInterface $userRepository;

    /**
     * @param FormFactory $formFactory
     * @param RequestStack $request
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(FormFactory $formFactory, RequestStack $request, UserRepositoryInterface $userRepository)
    {
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->userRepository = $userRepository;
    }


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

<?php

namespace Aropixel\AdminBundle\Http\Action\User;

use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DeleteUserAction extends AbstractController
{

    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ){}

    public function __invoke(Request $request, User $user) : Response
    {
        if ($this->isCsrfTokenValid('delete_user'.$user->getId(), $request->request->get('_token'))) {
            $this->userRepository->remove($user, true);
            $this->addFlash('notice', "L'utilisateur a bien été supprimé.");
        }

        return $this->redirect($this->generateUrl('aropixel_admin_user_index'));
    }
}

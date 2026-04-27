<?php

namespace Aropixel\AdminBundle\Controller\User;

use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeleteUserAction extends AbstractController
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function __invoke(Request $request, User $user): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        if ($this->isCsrfTokenValid('delete__user' . $user->getId(), $request->request->get('_token'))) {
            $this->userRepository->remove($user, true);
            $this->addFlash('notice', $this->translator->trans('user.flash.deleted'));
        }

        return $this->redirect($this->generateUrl('aropixel_admin_user_index'));
    }
}

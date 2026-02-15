<?php

namespace Aropixel\AdminBundle\Controller\Reset;

use Aropixel\AdminBundle\Component\Reset\Request\RequestLauncherInterface;
use Aropixel\AdminBundle\Form\Security\Reset\RequestType;
use Aropixel\AdminBundle\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestFormAction extends AbstractController
{
    public function __construct(
        private readonly RequestLauncherInterface $requestLauncher,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(RequestType::class);

        $notFound = false;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $this->userRepository->findUserByEmail($email);

            if ($user) {
                $this->requestLauncher->reset($user);

                return $this->redirectToRoute('aropixel_admin_request_status', ['status' => RequestStatusAction::PENDING]);
            }

            $notFound = true;
        }

        return $this->render('@AropixelAdmin/Reset/request.html.twig', ['form' => $form->createView(), 'not_found' => $notFound]);
    }
}

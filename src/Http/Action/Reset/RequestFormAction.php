<?php

namespace Aropixel\AdminBundle\Http\Action\Reset;

use Aropixel\AdminBundle\Domain\Reset\Request\RequestLauncherInterface;
use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Http\Action\Reset\RequestStatusAction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class RequestFormAction extends AbstractController
{
    public function __construct(
        private readonly RequestLauncherInterface $requestLauncher,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(Request $request)
    {
        $form = $this->createForm(\AdminBundle\Http\Form\Reset\RequestType::class);

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

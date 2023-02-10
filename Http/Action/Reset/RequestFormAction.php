<?php

namespace Aropixel\AdminBundle\Http\Action\Reset;

use Aropixel\AdminBundle\Domain\Reset\Request\RequestLauncherInterface;
use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class RequestFormAction extends AbstractController
{
    private RequestLauncherInterface $requestLauncher;
    private UserRepositoryInterface $userRepository;

    /**
     * @param RequestLauncherInterface $requestLauncher
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(RequestLauncherInterface $requestLauncher, UserRepositoryInterface $userRepository)
    {
        $this->requestLauncher = $requestLauncher;
        $this->userRepository = $userRepository;
    }


    public function __invoke(Request $request)
    {
        $form = $this->createForm(\Aropixel\AdminBundle\Http\Form\Reset\RequestType::class);

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

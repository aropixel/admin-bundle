<?php

namespace Aropixel\AdminBundle\Http\Action\Reset;

use Aropixel\AdminBundle\Domain\Reset\Request\RequestLauncherInterface;
use Aropixel\AdminBundle\Form\Reset\RequestType;
use Aropixel\AdminBundle\Infrastructure\User\UserRepositoryProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class RequestFormAction extends AbstractController
{
    private RequestLauncherInterface $requestLauncher;
    private UserRepositoryProvider $userRepositoryProvider;


    /**
     * @param RequestLauncherInterface $requestLauncher
     * @param UserRepositoryProvider $userRepositoryProvider
     */
    public function __construct(RequestLauncherInterface $requestLauncher, UserRepositoryProvider $userRepositoryProvider)
    {
        $this->requestLauncher = $requestLauncher;
        $this->userRepositoryProvider = $userRepositoryProvider;
    }


    public function __invoke(Request $request)
    {
        $form = $this->createForm(RequestType::class);

        $notFound = false;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $email = $form->get('email')->getData();
            $user = $this->userRepositoryProvider->getUserRepository()->findOneBy(['email' => $email]);

            if ($user) {
                $this->requestLauncher->reset($user);
                return $this->redirectToRoute('aropixel_admin_reset_request_info');
            }

            $notFound = true;
        }

        return $this->render('@AropixelAdmin/Reset/request.html.twig', ['form' => $form->createView(), 'not_found' => $notFound]);
    }

}
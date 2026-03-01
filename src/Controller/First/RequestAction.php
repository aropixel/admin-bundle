<?php

namespace Aropixel\AdminBundle\Controller\First;

use Aropixel\AdminBundle\Component\Activation\Email\ActivationEmailSenderInterface;
use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Form\Security\Reset\FirstLoginType;
use Aropixel\AdminBundle\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestAction extends AbstractController
{
    public function __construct(
        private readonly ActivationEmailSenderInterface $activationEmailSender,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(FirstLoginType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            /** @var ?UserInterface $user */
            $user = $this->userRepository->findOneBy(['email' => $email]);
            if (null === $user) {
                $response = new Response();
                $response->setStatusCode(Response::HTTP_SEE_OTHER);

                return $this->render('@AropixelAdmin/First/request.html.twig',
                    [
                        'form' => $form->createView(),
                        'not_found' => true,
                        'already_initialized' => false,
                    ],
                    $response
                );
            }

            if ($user->isInitialized()) {
                $response = new Response();
                $response->setStatusCode(Response::HTTP_SEE_OTHER);

                return $this->render('@AropixelAdmin/First/request.html.twig',
                    [
                        'form' => $form->createView(),
                        'not_found' => false,
                        'already_initialized' => true,
                    ],
                    $response
                );
            }

            $this->activationEmailSender->sendActivationEmail($user);

            return $this->redirectToRoute('aropixel_admin_security_first_login_sent');
        }

        return $this->render('@AropixelAdmin/First/request.html.twig',
            [
                'form' => $form->createView(),
                'not_found' => false,
                'already_initialized' => false,
            ]
        );
    }
}

<?php

namespace Aropixel\AdminBundle\Http\Action\First;

use Aropixel\AdminBundle\Domain\Activation\Email\ActivationEmailSenderInterface;
use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Http\Form\Reset\FirstLoginType;
use Aropixel\AdminBundle\Infrastructure\User\PasswordInitializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestAction extends AbstractController
{
    public function __construct(
        private readonly ActivationEmailSenderInterface $activationEmailSender,
        private readonly PasswordInitializer $passwordInitializer,
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
                return $this->render('@AropixelAdmin/First/request.html.twig',
                    [
                        'form' => $form->createView(),
                        'not_found' => true,
                        'already_initialized' => false,
                    ]
                );
            }

            if ($user->isInitialized()) {
                return $this->render('@AropixelAdmin/First/request.html.twig',
                    [
                        'form' => $form->createView(),
                        'not_found' => false,
                        'already_initialized' => true,
                    ]
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

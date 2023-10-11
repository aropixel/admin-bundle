<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 10/10/2023 à 11:41
 */

namespace Aropixel\AdminBundle\Http\Action\First;

use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Http\Form\Reset\FirstLoginType;
use Aropixel\AdminBundle\Repository\UserRepository;
use Aropixel\AdminBundle\Security\ActivationEmailSenderInterface;
use Aropixel\AdminBundle\Security\PasswordInitializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class RequestAction extends AbstractController
{
    /** @var \Aropixel\AdminBundle\Security\ActivationEmailSenderInterface */
    private $activationEmailSender;

    /** @var PasswordInitializer  */
    private $passwordInitializer;

    /** @var UserRepository  */
    private $userRepository;

    public function __construct(
        ActivationEmailSenderInterface $activationEmailSender,
        PasswordInitializer $passwordInitializer,
        UserRepository $userRepository
    ) {
        $this->activationEmailSender = $activationEmailSender;
        $this->passwordInitializer = $passwordInitializer;
        $this->userRepository = $userRepository;
    }

    public function __invoke(Request $request)
    {
        $form = $this->createForm(FirstLoginType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            /** @var UserInterface $user */
            $email = $form->get('email')->getData();
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

            if (!$this->passwordInitializer->stillPendingPasswordCreation($user)) {
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

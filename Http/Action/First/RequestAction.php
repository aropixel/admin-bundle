<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 10/10/2023 à 11:41
 */

namespace Aropixel\AdminBundle\Http\Action\First;

use Aropixel\AdminBundle\Domain\Activation\Email\ActivationEmailSenderInterface;
use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Http\Form\Reset\FirstLoginType;
use Aropixel\AdminBundle\Infrastructure\User\PasswordInitializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RequestAction extends AbstractController
{
    public function __construct(
        private readonly ActivationEmailSenderInterface $activationEmailSender,
        private readonly PasswordInitializer $passwordInitializer,
        private readonly UserRepositoryInterface $userRepository
    ) {}

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

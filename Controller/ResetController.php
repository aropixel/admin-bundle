<?php
/**
 * Créé par Aropixel @2020.
 * Par: Joël Gomez Caballe
 * Date: 04/05/2020 à 11:31
 */

namespace Aropixel\AdminBundle\Controller;

use Aropixel\AdminBundle\Email\ResetEmailSender;
use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Form\Reset\RequestType;
use Aropixel\AdminBundle\Form\Reset\ResetPasswordType;
use Aropixel\AdminBundle\Security\UniqueTokenGenerator;
use Aropixel\AdminBundle\Security\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class ResetController extends AbstractController
{

    /** @var ParameterBagInterface */
    private $parameterBag;

    /** @var UserManager */
    private $userManager;

    /** @var string */
    private $model;

    /** @var EntityManagerInterface */
    private $entityManager;


    /**
     * ResetController constructor.
     * @param ParameterBagInterface $parameterBag
     * @param UserManager $userManager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ParameterBagInterface $parameterBag, UserManager $userManager, EntityManagerInterface $entityManager)
    {
        $this->parameterBag = $parameterBag;
        $this->userManager = $userManager;

        $entities = $this->parameterBag->get('aropixel_admin.entities');
        $this->model = $entities[UserInterface::class];
        $this->entityManager = $entityManager;

    }


    public function requestReset(Request $request, UniqueTokenGenerator $generator, ResetEmailSender $resetEmailSender)
    {
        //
        $form = $this->createForm(RequestType::class);

        //
        $notFound = false;

        //
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->entityManager;

            //
            $email = $form->get('email')->getData();
            $user = $em->getRepository($this->model)->findOneBy(['email' => $email]);

            //
            if ($user) {
                $user->setPasswordResetToken($generator->generate());
                $user->setPasswordRequestedAt(new \DateTime());
                $em->flush();

                //
                $resetEmailSender->sendResetEmail($user);

                return $this->redirectToRoute('aropixel_admin_reset_request_info');
            }
            else {
                $notFound = true;
            }

        }

        //
        return $this->render('@AropixelAdmin/Reset/request.html.twig', ['form' => $form->createView(), 'not_found' => $notFound]);
    }


    public function requestResetInfo()
    {
        return $this->render('@AropixelAdmin/Reset/request_info.html.twig');
    }


    public function resetPassword(Request $request, string $token): Response
    {

        /** @var User $user */
        $user = $this->entityManager->getRepository($this->model)->findOneBy(['passwordResetToken' => $token]);
        if (null === $user) {
            throw new NotFoundHttpException('Token not found.');
        }

        $lifetime = new \DateInterval('P1D');
        if (!$user->isPasswordRequestNonExpired($lifetime)) {
            $this->handleExpiredToken($user);
            return $this->redirectToRoute('aropixel_admin_reset_result');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $form->get('password')->getViewData();
            $this->handleResetPassword($user, $password['first']);

            return $this->redirectToRoute('aropixel_admin_reset_result');
        }

        return $this->render('@AropixelAdmin/Reset/reset.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }


    public function resetSuccess()
    {
        return $this->render('@AropixelAdmin/Reset/reset_result.html.twig');
    }

    protected function handleExpiredToken(UserInterface $user)
    {
        $user->setPasswordResetToken(null);
        $user->setPasswordRequestedAt(null);

        $this->entityManager->flush();
    }

    protected function handleResetPassword(UserInterface $user, string $newPassword)
    {
        $user->setPlainPassword($newPassword);
        $user->setPasswordResetToken(null);
        $user->setPasswordRequestedAt(null);

        //
        $this->userManager->updateUser($user, true);

    }

}

<?php

namespace Aropixel\AdminBundle\Http\Action\User;

use Aropixel\AdminBundle\Http\Form\User\FormFactory;
use Aropixel\AdminBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class IndexUserAction extends AbstractController
{
    public function __construct(
        private readonly FormFactory $formFactory,
        private readonly UserRepository $userRepository
    ){}

    public function __invoke() : Response
    {
        $users = $this->userRepository->findAll();

        $columns = array(
            array('label' => 'Email', 'style' => ''),
            array('label' => 'Nom', 'style' => ''),
        );

        $delete_forms = array();
        foreach ($users as $user) {
            $deleteForm = $this->formFactory->createDeleteForm($user);
            $delete_forms[$user->getId()] = $deleteForm->createView();
        }

        return $this->render('@AropixelAdmin/User/Crud/index.html.twig', [
            'list_title' => 'Liste des administrateurs',
            'columns' => $columns,
            'users' => $users,
            'delete_forms' => $delete_forms
        ]);
    }
}
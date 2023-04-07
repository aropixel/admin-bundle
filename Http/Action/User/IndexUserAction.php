<?php

namespace Aropixel\AdminBundle\Http\Action\User;

use Aropixel\AdminBundle\Domain\User\UserRepositoryInterface;
use Aropixel\AdminBundle\Http\Form\User\FormFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class IndexUserAction extends AbstractController
{
    private FormFactory $formFactory;
    private UserRepositoryInterface $userRepository;

    /**
     * @param FormFactory $formFactory
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(FormFactory $formFactory, UserRepositoryInterface $userRepository)
    {
        $this->formFactory = $formFactory;
        $this->userRepository = $userRepository;
    }

    public function __invoke() : Response
    {
        $users = $this->userRepository->findBy([], ['createdAt' => 'ASC']);

        $columns = [
            ['label' => 'Email', 'style' => ''],
            ['label' => 'Nom', 'style' => ''],
        ];

        $delete_forms = [];
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

<?php

namespace Aropixel\AdminBundle\Controller\User;

use Aropixel\AdminBundle\Component\User\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class IndexUserAction extends AbstractController
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(): Response
    {
        $users = $this->userRepository->findBy([], ['createdAt' => 'ASC']);

        $columns = [
            ['label' => 'Email', 'style' => ''],
            ['label' => 'Nom', 'style' => ''],
        ];

        return $this->render('@AropixelAdmin/User/Crud/index.html.twig', [
            'list_title' => 'Liste des administrateurs',
            'columns' => $columns,
            'users' => $users,
        ]);
    }
}

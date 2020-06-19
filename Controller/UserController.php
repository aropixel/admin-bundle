<?php

namespace Aropixel\AdminBundle\Controller;

use Aropixel\AdminBundle\Form\Type\UserType;
use Aropixel\AdminBundle\Security\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Aropixel\AdminBundle\Entity\User;

/**
 * User controller.
 *
 * @Route("/user")
 */
class UserController extends AbstractController
{

    /**
     * Lists all User entities.
     *
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository(User::class)->findAll();

        $columns = array(
            array('label' => 'Email', 'style' => ''),
            array('label' => 'Nom', 'style' => ''),
        );

        $delete_forms = array();
        foreach ($entities as $entity) {
            $deleteForm = $this->createDeleteForm($entity);
            $delete_forms[$entity->getId()] = $deleteForm->createView();
        }

        return $this->render('@AropixelAdmin/User/Crud/index.html.twig', array(
            'list_title' => 'Liste des administrateurs',
            'columns' => $columns,
            'users' => $entities,
            'delete_forms' => $delete_forms,
        ));
    }



    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function create(Request $request, UserManager $userManager)
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user, array(
            'security.authorization_checker' => $this->get('security.authorization_checker'),
            'new' => true,
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Vérifie que l'utilisateur n'existe pas déjà
            $exists = $userManager->findUserByEmail($user->getEmail());
            if ($exists) {
                $this->addFlash('error', 'Cet email est déjà utilisé pour un utilisateur.');
                return $this->render('@AropixelAdmin/User/Crud/form.html.twig', array(
                    'user' => $user,
                    'form' => $form->createView(),
                ));
            }

            //
            $userManager->updateUser($user, true);

            //
            $this->addFlash('notice', 'Votre utilisateur a bien été enregistré.');

            return $this->redirectToRoute('user_edit', array('id' => $user->getId()));
        }

        return $this->render('@AropixelAdmin/User/Crud/form.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));

    }


    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, UserManager $userManager)
    {

        $deleteForm = $this->createDeleteForm($user);
        $editForm = $this->createForm(UserType::class, $user, array(
            'security.authorization_checker' => $this->get('security.authorization_checker')
        ));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            //
            $userManager->updateUser($user);

            //
            $this->addFlash('notice', 'Votre utilisateur a bien été enregistré.');

            return $this->redirectToRoute('user_edit', array('id' => $user->getId()));
        }

        return $this->render('@AropixelAdmin/User/Crud/form.html.twig', array(
            'user'   => $user,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }


    /**
     * Deletes a User entity.
     *
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user)
    {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->addFlash('notice', 'Votre utilisateur a bien été supprimé.');

            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();

        }

        return $this->redirect($this->generateUrl('user_index'));
    }


    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\FormInterface The form
     */
    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}

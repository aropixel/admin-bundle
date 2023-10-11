<?php

namespace Aropixel\AdminBundle\Controller;

use Aropixel\AdminBundle\Entity\UserInterface;
use Aropixel\AdminBundle\Form\Type\UserType;
use Aropixel\AdminBundle\Security\ActivationEmailSenderInterface;
use Aropixel\AdminBundle\Security\PasswordInitializerInterface;
use Aropixel\AdminBundle\Security\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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

    /** @var ParameterBagInterface */
    private $parameterBag;

    /** @var string */
    private $model = User::class;

    /** @var string */
    private $form = UserType::class;

    /**
     * UserController constructor.
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;

        $entities = $this->parameterBag->get('aropixel_admin.entities');
        $forms = $this->parameterBag->get('aropixel_admin.forms');

        $this->model = $entities[UserInterface::class];
        $this->form = $forms[UserInterface::class];

    }


    /**
     * Lists all User entities.
     *
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository($this->model)->findAll();

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
    public function create(Request $request, ActivationEmailSenderInterface $activationEmailSender, UserManager $userManager)
    {
        $user = $userManager->createUser();

        $form = $this->createForm($this->form, $user, array(
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
                    'sendButton' => false,
                    'form' => $form->createView(),
                ));
            }

            $userManager->updateUser($user, true);
            $activationEmailSender->sendActivationEmail($user);

            //
            $this->addFlash('notice', 'Votre utilisateur a bien été enregistré. Un email lui a été envoyé pour qu\'il puisse finaliser l\'ouverture de son compte.');

            return $this->redirectToRoute('user_edit', array('id' => $user->getId()));
        }

        return $this->render('@AropixelAdmin/User/Crud/form.html.twig', array(
            'user' => $user,
            'sendButton' => false,
            'form' => $form->createView(),
        ));

    }


    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, UserManager $userManager, PasswordInitializerInterface $passwordInitializer, $id)
    {
        //
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository($this->model)->find($id);

        //
        if (is_null($user)) {
            throw $this->createNotFoundException();
        }

        $deleteForm = $this->createDeleteForm($user);
        $editForm = $this->createForm($this->form, $user);
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
            'sendButton' => $passwordInitializer->stillPendingPasswordCreation($user),
            'delete_form' => $deleteForm->createView(),
        ));
    }


    /**
     * Deletes a User entity.
     *
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, $id)
    {
        //
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository($this->model)->find($id);

        //
        if (is_null($user)) {
            throw $this->createNotFoundException();
        }

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

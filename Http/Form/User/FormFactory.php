<?php

namespace Aropixel\AdminBundle\Http\Form\User;

use Aropixel\AdminBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;

class FormFactory extends AbstractController
{
    /**
     * Creates a form to delete a User entity by id.
     */
    public function createDeleteForm(User $user) : FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('aropixel_admin_user_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }


}
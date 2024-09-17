<?php

namespace Aropixel\AdminBundle\Form\Type;

use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserImage;
use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Aropixel\AdminBundle\Infrastructure\User\PasswordInitializer;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function __construct(
        private readonly PasswordInitializer $passwordInitializer,
        private readonly Security $security
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userToEdit = $builder->getData();

        $builder
            ->add('email', EmailType::class)
            ->add('enabled', ToggleSwitchType::class, ['label' => 'users.form.field.enabled', 'disabled' => !$userToEdit->getId() || $this->passwordInitializer->stillPendingPasswordCreation($userToEdit)])
            ->add('lastName', null, ['label' => 'users.form.field.last_name'])
            ->add('firstName', null, ['label' => 'users.form.field.first_name'])
            ->add('image', ImageType::class, [
                'label' => 'users.form.avatar',
                'data_class' => UserImage::class,
                'required' => false,
            ])
        ;

        $userLogged = $this->security->getUser();
        if ($userLogged->getId() == $userToEdit->getId()) {
            $builder
                ->add('plainPassword', RepeatedType::class, ['type' => PasswordType::class, 'required' => false, 'invalid_message' => 'users.form.field.password.invalid_message', 'first_options' => ['label' => 'users.form.field.password.change_password'], 'second_options' => ['label' => 'users.form.field.password.confirm_password']])
            ;
        }

        // If the user is granted
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            $builder
                ->add('superAdmin', ChoiceType::class, ['choices' => ['text.yes' => '1', 'text.no' => '0'], 'empty_data' => 'text.no', 'label' => 'users.form.field.super_admin', 'label_attr' => ['class' => 'radio-inline'], 'expanded' => true])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => User::class, 'new' => false]);
    }

}

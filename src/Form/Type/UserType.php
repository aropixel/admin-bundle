<?php

namespace Aropixel\AdminBundle\Form\Type;

use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserImage;
use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Aropixel\AdminBundle\Infrastructure\User\PasswordInitializer;
use Aropixel\AdminBundle\Form\Type\ToggleSwitchType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

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
            ->add('enabled', ToggleSwitchType::class, ['label' => new TranslatableMessage('Enabled'), 'disabled' => !$userToEdit->getId() || $this->passwordInitializer->stillPendingPasswordCreation($userToEdit)])
            ->add('lastName', null, ['label' => new TranslatableMessage('Last name')])
            ->add('firstName', null, ['label' => new TranslatableMessage('First name')])
            ->add('image', ImageType::class, [
                'label' => new TranslatableMessage('Avatar'),
                'data_class' => UserImage::class,
                'required' => false,
            ])
        ;

        $userLogged = $this->security->getUser();
        if ($userLogged->getId() == $userToEdit->getId()) {
            $builder
                ->add('plainPassword', RepeatedType::class, ['type' => PasswordType::class, 'required' => false, 'invalid_message' => new TranslatableMessage('New password and confirmation must match.'), 'first_options' => ['label' => new TranslatableMessage('Change password')], 'second_options' => ['label' => new TranslatableMessage('Confirm new password')]])
            ;
        }

        // If the user is granted
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            $builder
                ->add('superAdmin', ChoiceType::class, ['choices' => ['Oui' => '1', 'Non' => '0'], 'empty_data' => 'Non', 'label' => new TranslatableMessage('Super Admin'), 'label_attr' => ['class' => 'radio-inline'], 'expanded' => true])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => User::class, 'new' => false]);
    }

    public function getName()
    {
        return 'aropixel_adminbundle_user';
    }
}

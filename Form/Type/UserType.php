<?php

namespace Aropixel\AdminBundle\Form\Type;

use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Entity\UserImage;
use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Aropixel\AdminBundle\Form\Type\ToggleSwitchType;
use Aropixel\AdminBundle\Infrastructure\User\PasswordInitializer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;


class UserType extends AbstractType
{

    public function __construct(
        private readonly PasswordInitializer $passwordInitializer,
        private readonly Security $security
    ) {}


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userToEdit = $builder->getData();

        $builder
            ->add('email', EmailType::class)
            ->add('enabled', ToggleSwitchType::class, array(
                'label' => "Actif",
                'disabled' => !$userToEdit->getId() || $this->passwordInitializer->stillPendingPasswordCreation($userToEdit),
            ))
            ->add('lastName', null, array('label' => 'Nom'))
            ->add('firstName', null, array('label' => 'Prénom'))
            ->add('image',ImageType::class, [
                'label' => "Avatar",
                'data_class' => UserImage::class,
                'required' => false,
            ])
        ;

        $userLogged = $this->security->getUser();
        if ($userLogged->getId() == $userToEdit->getId()) {

            $builder
                ->add('plainPassword', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'required' => false,
                    'invalid_message' => 'Le mot de passe et la confirmation doivent correspondre.',
                    'first_options'  => array('label' => 'Changer le mot de passe'),
                    'second_options' => array('label' => 'Confirmer le mot de passe'),
                ))
            ;
        }


        // If the user is granted
        if($this->security->isGranted('ROLE_SUPER_ADMIN')) {

            $builder
                ->add('superAdmin', ChoiceType::class, array(
                    'choices'  => array(
                        'Oui' => '1',
                        'Non' => '0',
                    ),
                    'empty_data' => 'Non',
                    'label' => 'Super Administrateur',
                    'label_attr' => array('class' => 'radio-inline'),
                    'expanded' => true
                ))
            ;

        }


    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
            'new' => false
        ));
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'aropixel_adminbundle_user';
    }
}

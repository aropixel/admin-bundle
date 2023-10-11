<?php

namespace Aropixel\AdminBundle\Form\Type;

use Aropixel\AdminBundle\Entity\User;
use Aropixel\AdminBundle\Security\PasswordInitializerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class UserType extends AbstractType
{

    /** @var Security */
    private $security;

    /** @var PasswordInitializerInterface */
    private $passwordInitializer;


    /**
     * @param Security $security
     * @param PasswordInitializerInterface $passwordInitializer
     */
    public function __construct(Security $security, PasswordInitializerInterface $passwordInitializer)
    {
        $this->security = $security;
        $this->passwordInitializer = $passwordInitializer;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userToEdit = $builder->getData();

        $builder
            ->add('email')
            ->add('enabled', ChoiceType::class, array(
                'choices'  => array(
                    'Oui' => '1',
                    'Non' => '0',
                ),
                'empty_data' => 'Non',
                'label' => 'Actif',
                'disabled' => !$userToEdit->getId() || $this->passwordInitializer->stillPendingPasswordCreation($userToEdit),
                'expanded' => true
            ))
            ->add('lastName', null, array('label' => 'Nom'))
            ->add('firstName', null, array('label' => 'Prénom'))
            ->add('createdAt', DateTimeType::class, array(
                'disabled' => true,
                'required' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'date_format' => 'yyyy-MM-dd',
                'years' => range(date('Y') - 50, date('Y') + 5)
            ))
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

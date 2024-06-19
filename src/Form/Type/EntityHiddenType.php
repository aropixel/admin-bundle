<?php

namespace Aropixel\AdminBundle\Form\Type;

use Aropixel\AdminBundle\Form\DataTransformer\EntityToHiddenTransformer;
use Aropixel\AdminBundle\Form\DataTransformer\MultipleEntityToHiddenTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityHiddenType extends AbstractType
{
    private $configs;

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!empty($options['multiple']) && true == $options['multiple']) {
            $builder->addModelTransformer(new MultipleEntityToHiddenTransformer($this->em, $options['class']));
        } else {
            $builder->addModelTransformer(new EntityToHiddenTransformer($this->em, $options['class']));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['class'])
        ;
    }

    public function getParent()
    {
        return HiddenType::class;
    }

    public function getName()
    {
        return 'entity_hidden';
    }
}

<?php

namespace Aropixel\AdminBundle\Form\Type;

use Aropixel\AdminBundle\Form\DataTransformer\EntityToHiddenTransformer;
use Aropixel\AdminBundle\Form\DataTransformer\MultipleEntityToHiddenTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType that stores an entity ID in a hidden field.
 *
 * It uses a DataTransformer to convert the entity to its ID and vice-versa.
 *
 * Twig block: hidden_widget (inherited from HiddenType)
 *
 * Options:
 * - class: The entity class name (required).
 * - multiple: Whether to handle a collection of entities (default: false).
 *
 * Usage example:
 * $builder->add('category', EntityHiddenType::class, [
 *     'class' => Category::class,
 * ]);
 */
class EntityHiddenType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!empty($options['multiple']) && true == $options['multiple']) {
            $builder->addModelTransformer(new MultipleEntityToHiddenTransformer($this->em, $options['class']));
        } else {
            $builder->addModelTransformer(new EntityToHiddenTransformer($this->em, $options['class']));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['class'])
        ;
    }

    public function getParent(): ?string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_entity_hidden';
    }
}

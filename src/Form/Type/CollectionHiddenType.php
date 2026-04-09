<?php

namespace Aropixel\AdminBundle\Form\Type;

use Aropixel\AdminBundle\Form\DataTransformer\EntityToHiddenTransformer;
use Aropixel\AdminBundle\Form\DataTransformer\MultipleEntityToHiddenTransformer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType that stores a collection of entities in a hidden select field.
 *
 * Twig block: aropixel_admin_collection_hidden_row
 *
 * Options:
 * - class: The entity class (required).
 * - choice_label: The property name or callback to display as label (default: 'label').
 * - multiple: Whether to allow multiple selection (default: false).
 *
 * Usage example:
 * $builder->add('tags', CollectionHiddenType::class, [
 *     'class' => Tag::class,
 *     'multiple' => true,
 * ]);
 */
class CollectionHiddenType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $em,
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

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['multiple'] = $options['multiple'] ?: false;

        $entity = $form->getData();
        $choices = [];
        if ($entity) {
            if ($entity instanceof PersistentCollection || $entity instanceof ArrayCollection || \is_array($entity)) {
                foreach ($entity as $_entity) {
                    if (\is_callable($options['choice_label'])) {
                        $label = $options['choice_label']($_entity);
                    } else {
                        $label = $_entity->{'get' . ucfirst((string) $options['choice_label'])}();
                    }
                    $choices[] = ['value' => $_entity->getId(), 'idWs' => $_entity->getIdWs(), 'label' => $label];
                }
                $view->vars['entities'] = $entity;
            } else {
                if (\is_callable($options['choice_label'])) {
                    $label = $options['choice_label']($entity);
                } else {
                    $label = $entity->{'get' . ucfirst((string) $options['choice_label'])}();
                }
                $choices[] = ['value' => $entity->getId(), 'idWs' => $entity->getIdWs(), 'label' => $label];
                $view->vars['entity'] = $entity;
            }
        }

        $view->vars['choices'] = $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['choice_label' => 'label', 'multiple' => false])
            ->setRequired(['class'])
            ->setDefaults(['placeholder' => ''])
        ;
    }

    public function getParent(): ?string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_collection_hidden';
    }
}

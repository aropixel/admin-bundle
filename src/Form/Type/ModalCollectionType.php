<?php

namespace Aropixel\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModalCollectionType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['button_add_label'] = $options['button_add_label'];
        $view->vars['columns'] = $options['columns'];
        $view->vars['display_field'] = $options['display_field'];
        $view->vars['modal_title'] = $options['modal_title'];
        $view->vars['sortable'] = $options['sortable'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'button_add_label' => 'Ajouter un élément',
            'columns' => [], // ['Titre' => 'title', 'Position' => 'position']
            'display_field' => null, // Le champ à afficher dans la colonne principale du tableau
            'modal_title' => 'Détails de l\'élément',
            'sortable' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'by_reference' => false,
        ]);
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_modal_collection';
    }
}

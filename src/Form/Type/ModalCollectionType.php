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

        // On enveloppe les closures pour qu'elles soient appelables dans Twig via .call()
        $render_columns = [];
        foreach ($options['render_columns'] as $column => $render) {
            if (is_callable($render)) {
                $render = new class($render) {
                    private $callback;
                    public function __construct($callback) { $this->callback = $callback; }
                    public function call(...$args) { return ($this->callback)(...$args); }
                };
            }
            $render_columns[$column] = $render;
        }

        $view->vars['render_columns'] = $render_columns;
        $view->vars['display_columns'] = $options['display_columns'];
        $view->vars['modal_title'] = $options['modal_title'];
        $view->vars['sortable'] = $options['sortable'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'button_add_label' => 'Ajouter un élément',
            'columns' => [], // ['Titre' => 'title', 'Position' => 'position']
            'render_columns' => [], // ['title' => 'custom_block_name']
            'display_columns' => [], // Les champs à afficher en valeur plutôt qu'en widget
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

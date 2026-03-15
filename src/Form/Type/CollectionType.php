<?php

namespace Aropixel\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollectionType extends AbstractType
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

        // On détermine si on doit afficher la modale
        $use_modal = $options['use_modal'];
        if ($use_modal === null) {
            // Si c'est auto, on vérifie si des champs ne sont pas rendus en colonne
            $use_modal = false;
            $prototype = $form->getConfig()->getAttribute('prototype');
            if ($prototype) {
                foreach ($prototype as $child) {
                    $childName = $child->getName();
                    // On vérifie si ce champ est dans les colonnes (et n'est pas display_only ou render_custom)
                    $is_in_columns = in_array($childName, $options['columns']);
                    $is_displayed_as_value = in_array($childName, $options['display_columns']);
                    $has_custom_render = isset($options['render_columns'][$childName]);

                    if (!$is_in_columns || $is_displayed_as_value || $has_custom_render) {
                        $use_modal = true;
                        break;
                    }
                }
            }
        }
        $view->vars['use_modal'] = $use_modal;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'button_add_label' => 'Ajouter un élément',
            'columns' => [], // ['Titre' => 'title', 'Position' => 'position']
            'render_columns' => [], // ['title' => function($field, $item) { ... }]
            'display_columns' => [], // Les champs à afficher en valeur plutôt qu'en widget
            'modal_title' => 'Détails de l\'élément',
            'sortable' => true,
            'use_modal' => null, // null = auto, true = force, false = disable
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'by_reference' => false,
        ]);
    }

    public function getParent(): string
    {
        return \Symfony\Component\Form\Extension\Core\Type\CollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_collection';
    }
}

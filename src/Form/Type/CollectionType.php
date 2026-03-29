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

        // Normalize and wrap columns
        $columns = [];
        foreach ($options['columns'] as $label => $config) {
            if (is_string($config)) {
                $config = ['field' => $config];
            }

            // Wrap render closure if present
            if (isset($config['render']) && is_callable($config['render'])) {
                $config['render'] = new class($config['render']) {
                    private $callback;
                    public function __construct($callback) { $this->callback = $callback; }
                    public function call(...$args) { return ($this->callback)(...$args); }
                };
            }

            $columns[$label] = $config;
        }

        $view->vars['columns'] = $columns;
        $view->vars['modal_title'] = $options['modal_title'];
        $view->vars['sortable'] = $options['sortable'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'button_add_label' => 'Ajouter un élément',
            'columns' => [], // ['Label' => 'field_path', 'Label' => ['field' => 'field_path', 'display' => 'label', 'render' => function($field, $item) { ... }]]
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
        return \Symfony\Component\Form\Extension\Core\Type\CollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_collection';
    }
}

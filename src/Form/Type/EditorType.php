<?php

namespace Aropixel\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * WYSIWYG editor FormType using QuillJS.
 *
 * Twig block: aropixel_editor_widget
 *
 * Options:
 * - toolbar: The toolbar configuration (default: 'full').
 *            Can be a string ('full', 'simple', or custom) or an array of Quill options.
 *
 * Usage:
 * $builder->add('content', EditorType::class, [
 *     'toolbar' => 'simple',
 * ]);
 */
class EditorType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $toolbar = $options['toolbar'];
        if (is_array($toolbar)) {
            $toolbar = json_encode($toolbar);
        }
        $view->vars['toolbar'] = $toolbar;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'toolbar' => 'full',
        ]);
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_editor';
    }
}

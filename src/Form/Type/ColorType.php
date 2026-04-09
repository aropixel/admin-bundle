<?php

namespace Aropixel\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType for a color picker input.
 *
 * Twig block: aropixel_admin_color_widget
 *
 * Options:
 * - format: The color format (hex, rgb, etc. Default: 'hex').
 *
 * Usage example:
 * $builder->add('mainColor', ColorType::class, [
 *     'label' => 'Brand Color',
 *     'format' => 'hex',
 * ]);
 */
class ColorType extends AbstractType
{
    public function getParent(): ?string
    {
        return TextType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['colorFormat'] = $options['format'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(['format' => 'hex'])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_color';
    }
}

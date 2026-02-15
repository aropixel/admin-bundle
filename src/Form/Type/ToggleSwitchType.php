<?php

namespace Aropixel\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * FormType for a Bootstrap-style toggle switch.
 *
 * Twig block: aropixel_admin_toggle_switch_row
 *
 * Usage example:
 * $builder->add('enabled', ToggleSwitchType::class, [
 *     'label' => 'Active',
 * ]);
 */
class ToggleSwitchType extends AbstractType
{
    public function getParent(): ?string
    {
        return CheckboxType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_toggle_switch';
    }
}

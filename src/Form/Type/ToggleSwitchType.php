<?php

namespace Aropixel\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ToggleSwitchType extends AbstractType
{
    public function getParent()
    {
        return CheckboxType::class;
    }

    public function getBlockPrefix()
    {
        return 'aropixel_admin_toggle_switch';
    }
}

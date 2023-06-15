<?php

namespace Aropixel\AdminBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


class ToggleSwitchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CheckboxType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'aropixel_admin_toggle_switch';
    }

}
<?php

namespace Aropixel\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType for video embed code with a preview.
 *
 * Twig block: aropixel_admin_video_row
 *
 * Usage example:
 * $builder->add('videoEmbed', VideoType::class, [
 *     'label' => 'YouTube/Vimeo Embed Code',
 * ]);
 */
class VideoType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['attr' => ['rows' => 8]]);
    }

    public function getParent(): ?string
    {
        return TextareaType::class;
    }
    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_video';
    }
}

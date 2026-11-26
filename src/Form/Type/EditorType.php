<?php

namespace Aropixel\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * WYSIWYG editor FormType using QuillJS.
 *
 * Twig block: aropixel_admin_editor_widget
 *
 * Options:
 * - toolbar: The toolbar configuration (default: 'full').
 *            Can be a string ('full', 'simple', or custom) or an array of Quill options.
 *
 * Usage:
 * $builder->add('content', EditorType::class, [
 *     'toolbar' => 'simple',
 * ]);
 *
 * An editor left empty is submitted as an empty string, not as the line Quill
 * always keeps (`<p><br></p>`): the field then maps to null like any textarea.
 */
class EditorType extends AbstractType
{
    /**
     * Elements that are content on their own, without any text.
     */
    private const CONTENT_ELEMENTS = '/<(img|iframe|video|audio|object|embed|hr|table)\b/i';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event): void {
            $data = $event->getData();
            if (\is_string($data) && self::isBlank($data)) {
                $event->setData('');
            }
        });
    }

    /**
     * True when the HTML holds neither text (non-breaking and zero-width spaces
     * included) nor a content element, such as `<p><br></p>`, `<h2><br></h2>` or `<p>&nbsp;</p>`.
     */
    public static function isBlank(string $html): bool
    {
        if (1 === preg_match(self::CONTENT_ELEMENTS, $html)) {
            return false;
        }

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return '' === preg_replace('/[\s\x{00A0}\x{200B}\x{FEFF}]+/u', '', $text);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $toolbar = $options['toolbar'];
        if (\is_array($toolbar)) {
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
        return 'aropixel_admin_editor';
    }
}

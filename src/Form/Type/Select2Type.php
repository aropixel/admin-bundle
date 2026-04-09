<?php

namespace Aropixel\AdminBundle\Form\Type;

use Aropixel\AdminBundle\Form\DataTransformer\EntityToHiddenTransformer;
use Aropixel\AdminBundle\Form\DataTransformer\MultipleEntityToHiddenTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * FormType providing a Select2 input with AJAX support.
 *
 * Twig block: aropixel_admin_select2_row
 *
 * Options:
 * - class: The entity class (required).
 * - route: The AJAX route name to fetch results (required).
 * - route_params: The AJAX route parameters (default: []).
 * - choice_label: The property name or callback to display as label (default: 'label').
 * - multiple: Whether to allow multiple selection (default: false).
 * - placeholder: The input placeholder.
 *
 * Usage example:
 * $builder->add('category', Select2Type::class, [
 *     'label' => 'Category',
 *     'class' => Category::class,
 *     'route' => 'admin_category_ajax_search',
 *     'choice_label' => 'title',
 * ]);
 */
class Select2Type extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RouterInterface $router
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!empty($options['multiple']) && true == $options['multiple']) {
            $builder->addViewTransformer(new MultipleEntityToHiddenTransformer($this->em, $options['class']));
        } else {
            $builder->addViewTransformer(new EntityToHiddenTransformer($this->em, $options['class']));
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // Only add the empty value option if this is not the case
        // if (null !== $options['placeholder']) {
        //     $view->vars['placeholder'] = $options['placeholder'];
        // }
        $view->vars['url'] = $this->router->generate($options['route'], $options['route_params']);
        $view->vars['multiple'] = $options['multiple'] ?: false;
        $view->vars['placeholder'] = $options['placeholder'] ?: false;

        $entity = $form->getNormData();
        $choices = [];
        if ($entity) {
            $value = $form->getViewData();
            if ($entity instanceof \Doctrine\ORM\PersistentCollection || $entity instanceof \Doctrine\Common\Collections\ArrayCollection || is_array($entity)) {
                foreach ($entity as $idx => $_entity) {
                    if (\is_callable($options['choice_label'])) {
                        $label = $options['choice_label']($_entity);
                    } else {
                        $label = $_entity->{'get' . ucfirst((string) $options['choice_label'])}();
                    }
                    $choices[] = ['value' => (is_array($value) ? ($value[$idx] ?? $_entity->getId()) : $_entity->getId()), 'label' => $label];
                }
            } else {
                if (\is_callable($options['choice_label'])) {
                    $label = $options['choice_label']($entity);
                } else {
                    $label = $entity->{'get' . ucfirst((string) $options['choice_label'])}();
                }
                $choices[] = ['value' => $value ?: $entity->getId(), 'label' => $label];
            }
        }

        $view->vars['choices'] = $choices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'choice_label' => 'label',
                'multiple' => false,
                'placeholder' => null,
                'route_params' => [],
                'data_class' => null,
            ])
            ->setRequired(['class', 'route'])
        ;
    }

    public function getParent(): ?string
    {
        return HiddenType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_select2';
    }
}

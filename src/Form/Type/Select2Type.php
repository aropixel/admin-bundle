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

class Select2Type extends AbstractType
{
    private $configs;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RouterInterface $router
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!empty($options['multiple']) && true == $options['multiple']) {
            $builder->addModelTransformer(new MultipleEntityToHiddenTransformer($this->em, $options['repository']));
        } else {
            $builder->addModelTransformer(new EntityToHiddenTransformer($this->em, $options['repository']));
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        // Only add the empty value option if this is not the case
        // if (null !== $options['placeholder']) {
        //     $view->vars['placeholder'] = $options['placeholder'];
        // }
        $view->vars['url'] = $this->router->generate($options['route']);
        $view->vars['multiple'] = $options['multiple'] ?: false;
        $view->vars['placeholder'] = $options['placeholder'] ?: false;

        $entity = $form->getData();
        $choices = [];
        if ($entity) {
            // if (is_callable($options['choice_label'])) {
            //     $view->vars['choice_label'] = $options['choice_label']($entity);
            // }
            // elseif ($entity instanceof \Doctrine\ORM\PersistentCollection || $entity instanceof \Doctrine\Common\Collections\ArrayCollection) {
            if ($entity instanceof \Doctrine\ORM\PersistentCollection || $entity instanceof \Doctrine\Common\Collections\ArrayCollection) {
                foreach ($entity as $_entity) {
                    if (\is_callable($options['choice_label'])) {
                        $label = $options['choice_label']($_entity);
                    } else {
                        $label = $_entity->{'get' . ucfirst((string) $options['choice_label'])}();
                    }
                    $choices[] = ['value' => $_entity->getId(), 'label' => $label];
                }
            } else {
                if (\is_callable($options['choice_label'])) {
                    $label = $options['choice_label']($entity);
                } else {
                    $label = $entity->{'get' . ucfirst((string) $options['choice_label'])}();
                }
                $choices[] = ['value' => $entity->getId(), 'label' => $label];
            }
        }

        $view->vars['choices'] = $choices;

        // $view->vars['label'] = '';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $defaults = $this->configs;

        $resolver
            ->setDefaults(['configs' => $defaults, 'choice_label' => 'label', 'multiple' => false, 'placeholder' => null])
            ->setRequired(['repository', 'route'])
        ;
    }

    public function getParent()
    {
        return HiddenType::class;
    }

    public function getName()
    {
        return 'select2';
    }
}

<?php

namespace Aropixel\AdminBundle\Form\Type;

use Aropixel\AdminBundle\Form\DataTransformer\EntityToHiddenTransformer;
use Aropixel\AdminBundle\Form\DataTransformer\MultipleEntityToHiddenTransformer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class CollectionHiddenType extends AbstractType
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
        $view->vars['multiple'] = $options['multiple'] ?: false;

        $entity = $form->getData();
        $choices = [];
        if ($entity) {
            if ($entity instanceof PersistentCollection || $entity instanceof ArrayCollection || \is_array($entity)) {
                foreach ($entity as $_entity) {
                    if (\is_callable($options['choice_label'])) {
                        $label = $options['choice_label']($_entity);
                    } else {
                        $label = $_entity->{'get' . ucfirst((string) $options['choice_label'])}();
                    }
                    $choices[] = ['value' => $_entity->getId(), 'idWs' => $_entity->getIdWs(), 'label' => $label];
                }
                $view->vars['entities'] = $entity;
            } else {
                if (\is_callable($options['choice_label'])) {
                    $label = $options['choice_label']($entity);
                } else {
                    $label = $entity->{'get' . ucfirst((string) $options['choice_label'])}();
                }
                $choices[] = ['value' => $entity->getId(), 'idWs' => $entity->getIdWs(), 'label' => $label];
                $view->vars['entity'] = $entity;
            }
        }

        $view->vars['choices'] = $choices;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // for BC with the "empty_value" option
        //        $placeholder = function (Options $options) {
        //            return $options['empty_value'];
        //        };

        $defaults = $this->configs;

        $resolver
            ->setDefaults(['configs' => $defaults, 'choice_label' => 'label', 'multiple' => false])
            ->setRequired(['repository'])
            ->setDefaults(['placeholder' => ''])
        ;
    }

    public function getParent()
    {
        return HiddenType::class;
    }

    public function getBlockPrefix()
    {
        return 'entity_collection_hidden';
    }
}

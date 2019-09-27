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
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;


class CollectionHiddenType extends AbstractType
{

    private $em;
    private $router;
    private $configs;


    public function __construct(EntityManagerInterface $em, RouterInterface $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!empty($options['multiple']) && $options['multiple']==true) {
            $builder->addModelTransformer(new MultipleEntityToHiddenTransformer($this->em, $options['repository']));
        } else {
            $builder->addModelTransformer(new EntityToHiddenTransformer($this->em, $options['repository']));
        }
    }


    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {

        //
        $view->vars['multiple'] = $options['multiple'] ? $options['multiple'] : false;


        $entity = $form->getData();
        $choices = array();
        if ($entity) {

            //
            if ($entity instanceof PersistentCollection || $entity instanceof ArrayCollection || is_array($entity)) {
                foreach ($entity as $_entity) {
                    if (is_callable($options['choice_label'])) {
                        $label = $options['choice_label']($_entity);
                    }
                    else {
                        $label = $_entity->{'get'.ucfirst($options['choice_label'])}();
                    }
                    $choices[] = array('value' => $_entity->getId(), 'idWs' => $_entity->getIdWs(), 'label' => $label);
                }
                $view->vars['entities'] = $entity;
            }
            else {

                if (is_callable($options['choice_label'])) {
                    $label = $options['choice_label']($entity);
                }
                else {
                    $label = $entity->{'get'.ucfirst($options['choice_label'])}();
                }
                $choices[] = array('value' => $entity->getId(), 'idWs' => $entity->getIdWs(), 'label' => $label);
                $view->vars['entity'] = $entity;
            }
        }

        $view->vars['choices'] = $choices;


    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {

        // for BC with the "empty_value" option
//        $placeholder = function (Options $options) {
//            return $options['empty_value'];
//        };

        $defaults = $this->configs;

        $resolver
            ->setDefaults(array(
                'configs'      => $defaults,
                'choice_label' => 'label',
                'multiple'     => false,
//                'placeholder'  => $placeholder,
            ))
            ->setRequired(array(
                'repository',
            ))
            ->setDefaults(array(
                'placeholder' => "",
            ))
        ;

    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return HiddenType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'entity_collection_hidden';
    }
}

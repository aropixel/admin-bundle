<?php

namespace Aropixel\AdminBundle\Http\Form\Type;

use Aropixel\AdminBundle\Http\Form\DataTransformer\EntityToHiddenTransformer;
use Aropixel\AdminBundle\Http\Form\DataTransformer\MultipleEntityToHiddenTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class EntityHiddenType extends AbstractType
{

    private $em;
    private $configs;


    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!empty($options['multiple']) && $options['multiple']==true) {
            $builder->addModelTransformer(new MultipleEntityToHiddenTransformer($this->em, $options['class']));
        } else {
            $builder->addModelTransformer(new EntityToHiddenTransformer($this->em, $options['class']));
        }
    }




    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver
            ->setRequired(array(
                'class',
            ));

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
    public function getName()
    {
        return 'entity_hidden';
    }
}

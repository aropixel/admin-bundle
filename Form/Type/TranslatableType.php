<?php

namespace Aropixel\AdminBundle\Form\Type;

use Aropixel\AdminBundle\Form\Subscriber\Translatable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class TranslatableType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var array
     */
    protected $locales;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param array $locales
     * @param string $locale
     */
    public function __construct(EntityManagerInterface $em, ValidatorInterface $validator, array $locales, $locale)
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->locales = $locales;
        $this->locale = $locale;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (! class_exists($options['personal_translation'])) {
            throw $this->getNoPersonalTranslationException($options['personal_translation']);
        }

        dump($options);
        $options['field'] = $options['field'] ?: $builder->getName();
        $builder->addEventSubscriber(
            new Translatable($builder->getFormFactory(), $this->em, $this->validator, $options)
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults($this->getDefaultOptions());
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function getDefaultOptions(array $options = array())
    {
        $options['remove_empty'] = true; // Personal Translations without content are removed
        $options['csrf_protection'] = false;
        $options['personal_translation'] = false; // Personal Translation class
        $options['locales'] = $this->locales; // the locales you wish to edit
        $options['required_locale'] = [$this->locale]; // the required locales cannot be blank
        $options['field'] = false; // the field that you wish to translate
        $options['widget'] = TextType::class; // change this to another widget like 'texarea' if needed
        $options['entity_manager_removal'] = true; // auto removes the Personal Translation thru entity manager
        $options['attr'] = [];

        return $options;
    }

    /**
     * @param string $translation
     *
     * @return \InvalidArgumentException
     */
    public function getNoPersonalTranslationException($translation)
    {
        return new \InvalidArgumentException(sprintf('Unable to find personal translation class: "%s"', $translation));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'translatable';
    }
}
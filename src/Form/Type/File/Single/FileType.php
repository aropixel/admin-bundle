<?php

namespace Aropixel\AdminBundle\Form\Type\File\Single;

use Aropixel\AdminBundle\Component\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\AttachedFile;
use Aropixel\AdminBundle\Entity\FileInterface;
use Aropixel\AdminBundle\Form\Type\EntityHiddenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType used to display a single file widget with a library modal.
 *
 * Twig block: aropixel_admin_file_row
 *
 * Options:
 * - data_class: The entity class storing the file association (e.g., AttachedFile).
 *
 * Usage example:
 * $builder->add('document', FileType::class, [
 *     'label' => 'Manual',
 *     'data_class' => ProductFile::class,
 * ]);
 */
class FileType extends AbstractType
{
    public function __construct(
        private readonly PathResolverInterface $pathResolver,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', EntityHiddenType::class, ['class' => FileInterface::class])
            ->add('title', HiddenType::class)
            ->add('alt', HiddenType::class)
        ;
    }

    /**
     * Pass the image URL to the view.
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var AttachedFile $data */
        $data = $form->getData();

        $fileUrl = null;
        if (null !== $data) {
            $fileUrl = $this->pathResolver->getFilePath($data->getFile());
        }

        // set an "image_url" variable that will be available when rendering this field
        $view->vars['attach_class'] = $form->getConfig()->getDataClass();
        $view->vars['file_url'] = $fileUrl;
        $view->vars['attachedFile'] = $data;
        $view->vars['accept'] = $options['accept'];
        $view->vars['max_size'] = $options['max_size'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'accept' => null,
            'max_size' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'aropixel_admin_file';
    }
}

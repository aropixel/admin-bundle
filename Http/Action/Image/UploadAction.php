<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Domain\Media\Image\Library\DataTable\DataTableRowFactory;
use Aropixel\AdminBundle\Domain\Media\Image\Library\Factory\ImageFactoryInterface;
use Aropixel\AdminBundle\Form\Type\Image\PluploadType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadAction extends AbstractController
{
    public function __construct(
        private readonly DataTableRowFactory $dataTableRowFactory,
        private EntityManagerInterface $entityManager,
        private readonly ImageFactoryInterface $imageFactory
    ) {
    }

    /**
     * Upload an Image.
     */
    public function __invoke(Request $request): Response
    {
        $image = $this->imageFactory->create();
        $form = $this->createForm(PluploadType::class, $image, [
            'action' => $this->generateUrl('image_upload'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->entityManager;
            $em->persist($image);
            $em->flush();

            $httpResponse = new Response(json_encode($this->dataTableRowFactory->createRow($image)));
            $httpResponse->headers->set('Content-Type', 'application/json');

            return $httpResponse;
        }

        $errors = [];
        $formErrors = $form->getErrors(true);
        foreach ($formErrors as $formError) {
            $errors[] = $formError->getMessage();
        }

        return new Response(implode('<br />', $errors), 500);
    }
}

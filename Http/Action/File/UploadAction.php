<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Domain\Media\File\Library\DataTable\DataTableRowFactory;
use Aropixel\AdminBundle\Domain\Media\File\Library\Factory\FileFactoryInterface;
use Aropixel\AdminBundle\Form\Type\File\PluploadType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadAction extends AbstractController
{
    public function __construct(
        private readonly DataTableRowFactory $dataTableRowFactory,
        private EntityManagerInterface $entityManager,
        private readonly FileFactoryInterface $fileFactory
    ) {
    }

    /**
     * Upload a File.
     */
    public function __invoke(Request $request): Response
    {
        $file = $this->fileFactory->create();
        $form = $this->createForm(PluploadType::class, $file, [
            'action' => $this->generateUrl('file_upload'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->entityManager;
            $em->persist($file);
            $em->flush();

            $httpResponse = new Response(json_encode($this->dataTableRowFactory->createRow($file)));
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

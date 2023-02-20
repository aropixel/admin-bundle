<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Entity\File;
use Aropixel\AdminBundle\Form\Type\File\PluploadFileType;
use Aropixel\AdminBundle\Infrastructure\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Services\Datatabler;
use Aropixel\AdminBundle\Services\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadAction extends AbstractController
{
    private PathResolverInterface $pathResolver;
    private EntityManagerInterface $entityManager;
    private FileManager $fileManager;

    /**
     * @param \Aropixel\AdminBundle\Infrastructure\Media\Resolver\PathResolverInterface $pathResolver
     * @param EntityManagerInterface $entityManager
     * @param FileManager $fileManager
     */
    public function __construct(PathResolverInterface $pathResolver, EntityManagerInterface $entityManager, FileManager $fileManager)
    {
        $this->pathResolver = $pathResolver;
        $this->entityManager = $entityManager;
        $this->fileManager = $fileManager;
    }


    /**
     * Upload a file
     */
    public function __invoke(Request $request, Datatabler $datatabler) : Response
    {

        $fileClassName = $this->fileManager->getFileClassName();

        $file = new $fileClassName();
        $form = $this->createForm(PluploadFileType::class, $file, [
            'action' => $this->generateUrl('file_upload'),
            'method' => 'POST',
        ]);

        $response = [];

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->entityManager;
            $em->persist($file);
            $em->flush();

            $response = $this->_dataTableElements($file);

        } else {

            $errors = [];
            $formErrors = $form->getErrors(true);
            foreach ($formErrors as $formError) {
                $errors[] = $formError->getMessage();
            }

            $http_response = new Response(implode('<br />', $errors), 500);
            return $http_response;

        }

        $http_response = new Response(json_encode($response));
        $http_response->headers->set('Content-Type', 'application/json');
        return $http_response;

    }


    private function _dataTableElements($file) {

        $filePath = $this->pathResolver->getPrivateAbsolutePath($file->getFilename(), File::UPLOAD_DIR);
        $bytes = @filesize($filePath);
        $sz = 'bkMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        $decimals = 2;
        $unite = @$sz[$factor];
        if ($unite=='b' || $unite=='k') {
            $decimals = 0;
        }
        $filesize = sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];

        $extension = $file->getExtension();
        $iconExt = "img/files/".$extension.".png";
        $iconDft = "img/files/file.png";
        $basePath = __DIR__.'/../../../../public/';
        if (file_exists($basePath.'/bundles/aropixeladmin/'.$iconExt)) {
            $icon = '/bundles/aropixeladmin/'.$iconExt;
        }
        else {
            $icon = '/bundles/aropixeladmin/'.$iconDft;
        }

        return array(
            $this->renderView('@AropixelAdmin/File/Datatabler/checkbox.html.twig', array('file' => $file)),
            $this->renderView('@AropixelAdmin/File/Datatabler/preview.html.twig', array('file' => $file, 'icon' => $icon)),
            $this->renderView('@AropixelAdmin/File/Datatabler/title.html.twig', array('file' => $file)),
            $file->getCreatedAt()->format('d/m/Y'),
            $this->renderView('@AropixelAdmin/File/Datatabler/properties.html.twig', array('file' => $file, 'filesize' => $filesize)),
            $this->renderView('@AropixelAdmin/File/Datatabler/button.html.twig', array('file' => $file))
        );

    }


}

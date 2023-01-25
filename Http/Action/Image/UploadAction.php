<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Form\Type\Image\PluploadType;
use Aropixel\AdminBundle\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Services\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class UploadAction extends AbstractController
{

    private $datatableFieds = [];

    public function __construct(
        private readonly PathResolverInterface $pathResolver,
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageManager $imageManager
    ){
        $this->datatableFieds = [
            ['label' => '', 'style' => 'width:50px;'],
            ['label' => '', 'style' => 'width:200px;'],
            ['field' => 'i.titre', 'label' => 'Titre'],
            ['field' => 'i.createdAt', 'label' => 'Date'],
            ['label' => '', 'style' => 'width:200px;']
        ];
    }

    /**
     * Upload an Image.
     */
    public function __invoke(Request $request) : Response
    {

        $imageClassName = $this->imageManager->getImageClassName();
        $image = new $imageClassName();
        $form = $this->createForm(PluploadType::class, $image, [
            'action' => $this->generateUrl('image_upload'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->entityManager;
            $em->persist($image);
            $em->flush();

            $response = $this->_dataTableElements($image);

            $http_response = new Response(json_encode($response));
            $http_response->headers->set('Content-Type', 'application/json');
            return $http_response;

        }
        else {

            $errors = [];
            $formErrors = $form->getErrors(true);
            foreach ($formErrors as $formError) {
                $errors[] = $formError->getMessage();
            }

            $http_response = new Response(implode('<br />', $errors), 500);
            return $http_response;
        }

    }

    private function _dataTableElements($image) {

        $imagePath = $this->pathResolver->getAbsolutePath(Image::UPLOAD_DIR, $image->getFilename());

        $bytes = @filesize($imagePath);
        $sz = 'bkMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        $decimals = 2;
        $unite = @$sz[$factor];
        if ($unite=='b' || $unite=='k') {
            $decimals = 0;
        }
        $filesize = sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
        list($width, $height) = getimagesize($imagePath);

        return array(
            $this->renderView('@AropixelAdmin/Image/Datatabler/checkbox.html.twig', ['image' => $image]),
            $this->renderView('@AropixelAdmin/Image/Datatabler/preview.html.twig', ['image' => $image]),
            $this->renderView('@AropixelAdmin/Image/Datatabler/title.html.twig', ['image' => $image]),
            $image->getCreatedAt()->format('d/m/Y'),
            $this->renderView('@AropixelAdmin/Image/Datatabler/properties.html.twig', ['image' => $image, 'filesize' => $filesize, 'width' => $width, 'height' => $height]),
            $this->renderView('@AropixelAdmin/Image/Datatabler/button.html.twig', ['image' => $image])
        );

    }


}

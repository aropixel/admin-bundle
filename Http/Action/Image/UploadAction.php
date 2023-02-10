<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Form\Type\Image\PluploadType;
use Aropixel\AdminBundle\Infrastructure\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Services\ImageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadAction extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ImageManager $imageManager;
    private PathResolverInterface $pathResolver;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ImageManager $imageManager
     * @param PathResolverInterface $pathResolver
     */
    public function __construct(EntityManagerInterface $entityManager, ImageManager $imageManager, PathResolverInterface $pathResolver)
    {
        $this->entityManager = $entityManager;
        $this->imageManager = $imageManager;
        $this->pathResolver = $pathResolver;
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

        $imagePath = $this->pathResolver->getPrivateAbsolutePath($image->getFilename(), Image::UPLOAD_DIR);

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

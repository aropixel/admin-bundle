<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Services\Datatabler;
use Aropixel\AdminBundle\Services\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class AjaxAction extends AbstractController
{
    private Datatabler $datatabler;
    private ImageManager $imageManager;
    private PathResolverInterface $pathResolver;

    private $datatableFieds = [];


    /**
     * @param Datatabler $datatabler
     * @param ImageManager $imageManager
     * @param PathResolverInterface $pathResolver
     */
    public function __construct(Datatabler $datatabler, ImageManager $imageManager, PathResolverInterface $pathResolver)
    {
        $this->datatabler = $datatabler;
        $this->imageManager = $imageManager;
        $this->pathResolver = $pathResolver;

        $this->datatableFieds = [
            ['label' => '', 'style' => 'width:50px;'],
            ['label' => '', 'style' => 'width:200px;'],
            ['field' => 'i.titre', 'label' => 'Titre'],
            ['field' => 'i.createdAt', 'label' => 'Date'],
            ['label' => '', 'style' => 'width:200px;']
        ];
    }


    /**
     * Lists all Image entities.
     */
    public function __invoke() : Response
    {

        $response = [];

        $imageClassName = $this->imageManager->getImageClassName();
        $this->datatabler->setRepository($imageClassName, $this->datatableFieds);

        if ($this->datatabler->isCalled()) {

            $images = $this->datatabler->getItems();

            foreach ($images as $image)
            {
                $response[] = $this->_dataTableElements($image);
            }
        }

        return $this->datatabler->getResponse($response);

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
            $this->renderView('@AropixelAdmin/Image/Datatabler/checkbox.html.twig', array('image' => $image)),
            $this->renderView('@AropixelAdmin/Image/Datatabler/preview.html.twig', array('image' => $image)),
            $this->renderView('@AropixelAdmin/Image/Datatabler/title.html.twig', array('image' => $image)),
            $image->getCreatedAt()->format('d/m/Y'),
            $this->renderView('@AropixelAdmin/Image/Datatabler/properties.html.twig', array('image' => $image, 'filesize' => $filesize, 'width' => $width, 'height' => $height)),
            $this->renderView('@AropixelAdmin/Image/Datatabler/button.html.twig', array('image' => $image))
        );

    }


}

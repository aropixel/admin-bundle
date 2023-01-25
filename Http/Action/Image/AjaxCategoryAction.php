<?php

namespace Aropixel\AdminBundle\Http\Action\Image;

use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Services\Datatabler;
use Aropixel\AdminBundle\Services\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class AjaxCategoryAction extends AbstractController
{

    private $datatableFieds = [];

    public function __construct(
        private readonly PathResolverInterface $pathResolver,
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
     * Lists all Image entities.
     */
    public function __invoke(Datatabler $datatabler, string $category) : Response
    {

        $response = [];

        $imageClassName = $this->imageManager->getImageClassName();
        $datatabler->setRepository($imageClassName, $this->datatableFieds);

        $qb = $datatabler->getQueryBuilder();
        $qb
            ->andWhere('i.category = :category')
            ->setParameter('category', $category)
        ;

        $datatabler->setQueryBuilder($qb, 'i');

        if ($datatabler->isCalled()) {

            $images = $datatabler->getItems();

            foreach ($images as $image)
            {
                $imagePath = $this->pathResolver->getAbsolutePath(Image::UPLOAD_DIR, $image->getFilename());
                if (file_exists($imagePath)) {
                    $response[] = $this->_dataTableElements($image);
                }

            }
        }

        return $datatabler->getResponse($response);

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

<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Entity\File;
use Aropixel\AdminBundle\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Services\Datatabler;
use Aropixel\AdminBundle\Services\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class AjaxAction extends AbstractController
{

    private $datatableFieds = [];

    public function __construct(
        private readonly PathResolverInterface $pathResolver,
        private readonly EntityManagerInterface $entityManager,
        private readonly FileManager $fileManager
    ){
        $this->datatableFieds = [
            ['label' => '', 'style' => 'width:50px;'],
            ['label' => '', 'style' => 'width:200px;'],
            ['field' => 'i.titre', 'label' => 'Titre'],
            ['field' => 'i.createdAt', 'label' => 'Date'],
            ['label' => '', 'style' => 'width:200px;'],
        ];
    }

    /**
     * Lists all file entities.
     */
    public function __invoke(Request $request, Datatabler $datatabler) : Response
    {

        $response = [];

        $isPublic = (boolean)$request->get('editor');

        $fileClassName = $this->fileManager->getFileClassName();
        $datatabler->setRepository($fileClassName, $this->datatableFieds);

        $qb = $datatabler->getQueryBuilder();
        $qb
            ->andWhere('f.public = :public')
            ->setParameter('public', $isPublic)
        ;

        if ($datatabler->isCalled()) {

            $files = $datatabler->getItems();
            foreach ($files as $file)
            {
                $response[] = $this->_dataTableElements($file);
            }

        }

        return $datatabler->getResponse($response);

    }


    private function _dataTableElements($file) {

        $filePath = $this->pathResolver->getAbsolutePath(File::UPLOAD_DIR, $file->getFilename());
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
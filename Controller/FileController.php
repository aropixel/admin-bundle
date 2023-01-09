<?php

namespace Aropixel\AdminBundle\Controller;

use Aropixel\AdminBundle\Domain\Entity\AttachFile;
use Aropixel\AdminBundle\Domain\Entity\File;
use Aropixel\AdminBundle\Domain\Entity\FileInterface;
use Aropixel\AdminBundle\Http\Form\Type\File\PluploadFileType;
use Aropixel\AdminBundle\Http\Form\Type\File\Single\FileType;
use Aropixel\AdminBundle\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Services\Datatabler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * File controller.
 *
 * @Route("/file")
 */
class FileController extends AbstractController
{

    private $datatableFieds = array();

    /** @var PathResolverInterface */
    private $pathResolver;

    /** @var EntityManagerInterface */
    private $entityManager;


    public function __construct(PathResolverInterface $pathResolver, EntityManagerInterface $entityManager) {

        $this->pathResolver = $pathResolver;
        $this->entityManager = $entityManager;
        $this->datatableFieds = array(
            array('label' => '', 'style' => 'width:50px;'),
            array('label' => '', 'style' => 'width:200px;'),
            array('field' => 'i.titre', 'label' => 'Titre'),
            array('field' => 'i.createdAt', 'label' => 'Date'),
            array('label' => '', 'style' => 'width:200px;'),
        );

    }

    private function getFileClassName()
    {
        $entities = $this->getParameter('aropixel_admin.entities');
        return $entities[FileInterface::class];
    }

    /**
     * Lists all Image entities.
     *
     * @Route("/list/ajax", name="file_ajax", methods={"GET"})
     */
    public function datatablerAction(Request $request, Datatabler $datatabler)
    {

        //
        $response = array();

        //
        $isPublic = (boolean)$request->get('editor');

        //
        $datatabler->setRepository($this->getFileClassName(), $this->datatableFieds);
        $qb = $datatabler->getQueryBuilder();
        $qb
            ->andWhere('f.public = :public')
            ->setParameter('public', $isPublic)
        ;


        if ($datatabler->isCalled()) {

            //
            $files = $datatabler->getItems();

            //
            foreach ($files as $file)
            {
                //

                //
                $response[] = $this->_dataTableElements($file);

            }
        }


        //
        return $datatabler->getResponse($response);

    }

    /**
     * Lists all File entities.
     *
     * @Route("/list/ajax/{category}", name="file_ajax_category", methods={"GET"})
     */
    public function datatablerWithCategoryAction(Request $request, Datatabler $datatabler, $category)
    {

        //
        $response = array();

        //
        $isPublic = (boolean)$request->get('editor');

        //
        $datatabler = $datatabler->setRepository($this->getFileClassName(), $this->datatableFieds);
        $qb = $datatabler->getQueryBuilder();
        $qb
            ->andWhere('f.category = :category')
            ->andWhere('f.public = :public')
            ->setParameter('category', $category)
            ->setParameter('public', $isPublic)
        ;

        //
        $datatabler->setQueryBuilder($qb, 'f');

        if ($datatabler->isCalled()) {

            //
            $files = $datatabler->getItems();

            //
            foreach ($files as $file)
            {
                //
                $filePath = $this->pathResolver->getAbsolutePath(File::UPLOAD_DIR, $file->getFilename());
                if (file_exists($filePath)) {
                    $response[] = $this->_dataTableElements($file);
                }

            }
        }


        //
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

    /**
     * Save file title.
     *
     * @Route("/save", name="file_save", options={"expose"=true}, methods={"POST"})
     */
    public function saveAction(Request $request)
    {

        $entity_id = $request->request->get('id');
        $titre = $request->request->get('titre');
        $em = $this->entityManager;
        $file = $em->getRepository($this->getFileClassName())->find($entity_id);

        if ($file) {

            $file->setTitre($titre);
            $em->flush();

        }

        return new Response($titre, Response::HTTP_OK);

    }


    /**
     * Attach an Image.
     *
     * @Route("/remove", name="file_delete", options={"expose"=true}, methods={"POST"})
     */
    public function removeAction(Request $request)
    {
        //
        $fichier_id = $request->get('file_id');
        $libraryClass = $request->get('category');

        //
        $em = $this->entityManager;
        $fichier = $em->getRepository(File::class)->find($fichier_id);

        //
        if ($fichier) {

            //
            $libraryEntity = new \ReflectionClass($libraryClass);
            if ($libraryEntity instanceof AttachFile) {

                //
                $attachedFiles = $em->getRepository($libraryClass)->findBy(array('file' => $fichier));

                //
                if (count($attachedFiles)) {

                    foreach ($attachedFiles as $attachedFile) {
                        $em->remove($attachedFile);
                    }
                    $em->flush();

                }

            }

            //
            $em->remove($fichier);
            $em->flush();

        }

        //
        return new Response('OK', Response::HTTP_OK);

    }


    /**
     * Upload an File.
     *
     * @Route("/upload", name="file_upload", options={"expose"=true}, methods={"POST"})
     */
    public function uploadAction(Request $request)
    {
        //
        $className = $this->getFileClassName();
        $file = new $className();
        $form = $this->createForm(PluploadFileType::class, $file, array(
            'action' => $this->generateUrl('file_upload'),
            'method' => 'POST',
        ));

        //
        $response = array();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->entityManager;
            $em->persist($file);
            $em->flush();

            $response = $this->_dataTableElements($file);
        }
        else {

            //
            $errors = [];
            $formErrors = $form->getErrors(true);
            foreach ($formErrors as $formError) {
                $errors[] = $formError->getMessage();
            }

            //
            $http_response = new Response(implode($errors, '<br />'), 500);
            return $http_response;
        }

        //
        $http_response = new Response(json_encode($response));
        $http_response->headers->set('Content-Type', 'application/json');
        return $http_response;
    }



    /**
     * Crop an Image.
     *
     * @Route("/title", name="file_title", options={"expose"=true}, methods={"POST"})
     */
    public function titleAction(Request $request)
    {
        //
        $file_id = $request->get('pk');
        $title = $request->get('value');

        //
        $em = $this->entityManager;

        //
        $image = $em->getRepository($this->getFileClassName())->find($file_id);
        $image->setTitre($title);
        $em->flush();


        //
        return new Response('Done', Response::HTTP_OK);
    }


    /**
     * Attach a File.
     *
     * @Route("/attach/file", name="file_attach", options={"expose"=true}, methods={"POST"})
     */
    public function attachFileAction(Request $request)
    {
        //
        $files = $request->get('files');
        $attachClass = $request->get('attach_class');
        $attachId = $request->get('attach_id');

        //
        $t_entity = explode('\\', $attachClass);
        $entity_name = array_pop($t_entity);    array_pop($t_entity);
        $short_namespace = implode('', $t_entity);
        $attachRepositoryName = $short_namespace.':'.$entity_name;
        $em = $this->entityManager;

        //
        if ($attachId) {
            $attachFile = $em->getRepository($attachRepositoryName)->find($attachId);
        }
        else {
            $attachFile = new $attachClass();
        }



        $html = '';
        foreach ($files as $fileId) {

            //
            $file = $em->getRepository('AropixelAdminBundle:File')->find($fileId);
            $attachFile->setFile($file);
            $attachFile->setTitle($file->getTitre());
            //
            $form = $this->createForm(FileType::class, $attachFile, array('data_class' => $attachClass));

            //
            $html.= $this->renderView('@AropixelAdmin/File/Widget/file.html.twig', array(
                'form'      => $form->createView(),
            ));

        }


        //
        return new Response($html, Response::HTTP_OK);
    }


}

<?php

namespace Aropixel\AdminBundle\Controller;

use Aropixel\AdminBundle\Form\Type\Image\Single\ImageType;
use Aropixel\AdminBundle\Services\Datatabler;
use Aropixel\AdminBundle\Services\ImageManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Entity\Crop;
use Aropixel\AdminBundle\Form\Type\Image\PluploadType;


/**
 * Image controller.
 *
 * @Route("/image")
 */
class ImageController extends Controller
{

    private $datatableFieds = array();


    public function __construct() {

        $this->datatableFieds = array(
            array('label' => '', 'style' => 'width:50px;'),
            array('label' => '', 'style' => 'width:200px;'),
            array('field' => 'i.titre', 'label' => 'Titre'),
            array('field' => 'i.createdAt', 'label' => 'Date'),
            array('label' => '', 'style' => 'width:200px;'),
        );

    }

    /**
     * Lists all Image entities.
     *
     * @Route("/list/ajax", name="image_ajax", methods={"GET"})
     */
    public function datatablerAction(Request $request, Datatabler $datatabler)
    {

        //
        $response = array();
        $em = $this->getDoctrine()->getManager();

        //
        $datatabler->setRepository('AropixelAdminBundle:Image', $this->datatableFieds);
        if ($datatabler->isCalled()) {

            //
            $images = $datatabler->getItems();

            //
            foreach ($images as $image)
            {
                //

                //
                $response[] = $this->_dataTableElements($image);

            }
        }


        //
        return $datatabler->getResponse($response);

    }

    /**
     * Lists all Image entities.
     *
     * @Route("/list/ajax/{category}", name="image_ajax_category", methods={"GET"})
     */
    public function datatablerWithCategoryAction(Request $request, Datatabler $datatabler, $category)
    {

        //
        $response = array();

        //
        $datatabler->setRepository('AropixelAdminBundle:Image', $this->datatableFieds);
        $qb = $datatabler->getQueryBuilder();
        $qb
            ->andWhere('i.category = :category')
            ->setParameter('category', $category)
        ;

        //
        $datatabler->setQueryBuilder($qb, 'i');

        if ($datatabler->isCalled()) {

            //
            $images = $datatabler->getItems();

            //
            foreach ($images as $image)
            {
                //
                if (file_exists($image->getAbsolutePath())) {
                    $response[] = $this->_dataTableElements($image);
                }

            }
        }


        //
        return $datatabler->getResponse($response);

    }


    private function _dataTableElements($image) {

        $bytes = @filesize($image->getAbsolutePath());
        $sz = 'bkMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        $decimals = 2;
        $unite = @$sz[$factor];
        if ($unite=='b' || $unite=='k') {
            $decimals = 0;
        }
        $filesize = sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
        list($width, $height) = getimagesize($image->getAbsolutePath());

        return array(
            $this->renderView('@AropixelAdmin/Image/Datatabler/checkbox.html.twig', array('image' => $image)),
            $this->renderView('@AropixelAdmin/Image/Datatabler/preview.html.twig', array('image' => $image)),
            $this->renderView('@AropixelAdmin/Image/Datatabler/title.html.twig', array('image' => $image)),
            $image->getCreatedAt()->format('d/m/Y'),
            $this->renderView('@AropixelAdmin/Image/Datatabler/properties.html.twig', array('image' => $image, 'filesize' => $filesize, 'width' => $width, 'height' => $height)),
            $this->renderView('@AropixelAdmin/Image/Datatabler/button.html.twig', array('image' => $image))
        );

    }

    /**
     * Lists all Image entities.
     *
     * @Route("/", name="image_list", options={"expose"=true}, methods={"GET"})
     * @Template("AropixelAdminBundle:Image\Modal:thumbnail-list.html.twig")
     */
    public function listAction(Request $request)
    {
        //
        $category = $request->get('category');

        //
        $repository = $this->getDoctrine()->getRepository('AropixelAdminBundle:Image');
        $images = $repository->findByCategory($category);

        //
        return array('images' => $images);
    }


    /**
     * Count Image entities.
     *
     * @Route("/total", name="image_total", options={"expose"=true}, methods={"GET"})
     */
    public function totalAction(Request $request)
    {
        //
        $category = $request->get('category');

        //
        $repository = $this->getDoctrine()->getRepository('AropixelAdminBundle:Image');
        $nbs = $repository->count($category);

        //
        return new Response($nbs, Response::HTTP_OK);

    }


    /**
     * Upload an Image.
     *
     * @Route("/upload", name="image_upload", options={"expose"=true}, methods={"POST"})
     */
    public function uploadAction(Request $request)
    {
        //
        $image = new Image();
        $form = $this->createForm(PluploadType::class, $image, array(
            'action' => $this->generateUrl('image_upload'),
            'method' => 'POST',
        ));

        //
        $response = array();
        $form->handleRequest($request);
        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($image);
            $em->flush();

            $response = $this->_dataTableElements($image);
        }

        //
        $http_response = new Response(json_encode($response));
        $http_response->headers->set('Content-Type', 'application/json');
        return $http_response;

    }


    /**
     * Attach an Image.
     *
     * @Route("/attach/image", name="image_attach", options={"expose"=true}, methods={"POST"})
     */
    public function attachImageAction(Request $request)
    {
        //
        $images = $request->get('images');
        $entityClass = $request->get('entity_class');
        $attachClass = $request->get('attach_class');
        $attachId = $request->get('attach_id');

        //
        $t_entity = explode('\\', $attachClass);
        $entity_name = array_pop($t_entity);    array_pop($t_entity);
        $short_namespace = implode('', $t_entity);
        $attachRepositoryName = $short_namespace.':'.$entity_name;

        //
        if ($attachId) {
            $attachImage = $this->getDoctrine()->getRepository($attachRepositoryName)->find($attachId);
        }
        else {
            $attachImage = new $attachClass();
        }



        $html = '';
        foreach ($images as $image_id) {

            //
            $image = $this->getDoctrine()->getRepository('AropixelAdminBundle:Image')->find($image_id);
            $attachImage->setImage($image);

            //
            $form = $this->createForm(ImageType::class, $attachImage, array('data_class' => $attachClass));

            //
            $html.= $this->renderView('@AropixelAdmin/Image/Widget/image.html.twig', array(
                'form'      => $form->createView(),
            ));

        }


        //
        return new Response($html, Response::HTTP_OK);
    }


    /**
     * Attach an Image.
     *
     * @Route("/attach/gallery", name="gallery_attach", options={"expose"=true}, methods={"POST"})
     */
    public function attachGalleryAction(Request $request)
    {
        //
        $entity_id = $request->get('id');
        $routeName = $request->get('route');
        $images = $request->get('images');
        $multiple = $request->get('multiple');
        $category = $request->get('category');
        $position = $request->get('position');
        $t_entity = explode('\\', $category);

        //
        $entity_name = array_pop($t_entity);    array_pop($t_entity);
        $short_namespace = implode('', $t_entity);

        //
        $entity = $this->getDoctrine()->getRepository($short_namespace.':'.$entity_name)->find($entity_id);

        //
        $html = '';
        foreach ($images as $image_id) {

            $image = $this->getDoctrine()->getRepository('AropixelAdminBundle:Image')->find($image_id);


            $html.= $this->renderView('@AropixelAdmin/Image/Widget/gallery.html.twig', array(
                'id'        => $entity_id,
                'category'  => $category,
                'image'     => $image,
                'entity'    => $entity,
                'position'  => $position,
                'routeName' => $routeName,
                'multiple'  => $multiple,
            ));

            $position++;
        }


        //
        return new Response($html, Response::HTTP_OK);
    }


    /**
     * Attach an Image.
     *
     * @Route("/editor", name="image_editor", options={"expose"=true}, methods={"POST"})
     */
    public function attachEditorAction(Request $request, ImageManager $imageManager)
    {

        //
        $html = "";

        //
        $images_id = $request->get('images', array());
        $width = $request->get('width', 300);
        $decoupe = $request->get('filter', null);
        $alt = $request->get('alt', '');

        if ($width=='customfilter') {
            $width = null;
        }

        if ($width=='auto') {
            $width = null;
            $decoupe = null;
        }

        //
        if (count($images_id)) {

            //
            if (strlen($alt)) 			$alt = ' alt="'.$alt.'"';

            //
            foreach ($images_id as $image_id) {


                //
                $image = $this->getDoctrine()->getRepository('AropixelAdminBundle:Image')->find($image_id);
                $url = $imageManager->editorResize($image, $width, $decoupe);

                //
                $class = "";
                $widthTag = ' width="'.$width.'"';
                if ($width=='100pc') {
                    $class = ' class="img-fluid img-responsive"';
                    $widthTag = '';
                }
                if (is_null($width)) {
                    $widthTag = '';
                }

                //
                $html.= '<img src="'.$url.'" '.$widthTag.$alt.$class.' />';

            }
        }

        //
        return new Response($html, Response::HTTP_OK);
    }


    /**
     * Attach an Image.
     *
     * @Route("/detach", name="image_detach", options={"expose"=true}, methods={"POST"})
     * @Template("AropixelAdminBundle:Main/Image:thumbnail.html.twig")
     */
    public function detachAction(Request $request)
    {
        //
        $entity_id = $request->get('entity_id');
        $image_id = $request->get('image_id');
        $t_entity = explode('\\', $request->get('category'));

        //
        $entity_name = array_pop($t_entity);    array_pop($t_entity);
        $short_namespace = implode('', $t_entity);

        //
        $em = $this->getDoctrine()->getManager();

        //
        if ($entity_id) {

            $entity = $this->getDoctrine()->getRepository($short_namespace.':'.$entity_name)->find($entity_id);
            $image = $this->getDoctrine()->getRepository('AropixelAdminBundle:Image')->find($image_id);

            if ($entity && $image) {

                $entity->setImage(null);
                $em->flush();

            }

        }

        return array(
            'id' => $entity_id,
            'image' => false,
            'category' => $request->get('category'),
        );
    }


    /**
     * Attach an Image.
     *
     * @Route("/remove", name="image_delete", options={"expose"=true}, methods={"POST"})
     */
    public function removeAction(Request $request)
    {
        //
        $image_id = $request->get('image_id');
        $t_entity = explode('\\', $request->get('category'));

        //
        $entity_name = array_pop($t_entity);    array_pop($t_entity);
        $short_namespace = implode('', $t_entity);

        //
        $em = $this->getDoctrine()->getManager();
        $image = $this->getDoctrine()->getRepository('AropixelAdminBundle:Image')->find($image_id);

        //
        if ($image) {

            //
            $attachedImages = $this->getDoctrine()->getRepository($short_namespace.':'.$entity_name)->findBy(array('image' => $image));

            //
            if (count($attachedImages)) {

                foreach ($attachedImages as $attachedImage) {
                    $em->remove($attachedImage);
                }
                $em->flush();

            }

            //
            $em->remove($image);
            $em->flush();

        }

        //
        return new Response('OK', Response::HTTP_OK);

    }





    /**
     * Attach an Image.
     *
     * @Route("/settings", name="image_settings", options={"expose"=true}, methods={"GET"})
     * @Template("AropixelAdminBundle:Image\Modal:settings.html.twig")
     */
    public function settingsAction(Request $request)
    {

        //
        $image = false;
        $image_id = $request->get('image_id', false);

        //
        if ($image_id) {

            $image = $this->getDoctrine()->getRepository('AropixelAdminBundle:Image')->find($image_id);

        }

        //
        return array('image' => $image);
    }




    /**
     * Crop an Image.
     *
     * @Route("/crop", name="image_crop", options={"expose"=true}, methods={"GET"})
     * @Template("AropixelAdminBundle:Main/Image/Modals:crop.html.twig")
     */
    public function cropAction(Request $request, ImageManager $imageManager)
    {
        //
        $route_name = $request->get('route');

        //
        $image_id = $request->get('image_id');
        $image = $this->getDoctrine()->getRepository('AropixelAdminBundle:Image')->find($image_id);

        //
        $filters = $imageManager->getCropFilters($route_name, $image);

        //
        return array('filters' => $filters, 'image' => $image);
    }


    /**
     * Save crop info of an Image.
     *
     * @Route("/save_infos", name="image_crop_save", options={"expose"=true}, methods={"POST"})
     */
    public function cropSaveAction(Request $request, ImageManager $imageManager)
    {

        //
        $image_id = $request->get('image_id');
        $filter = $request->get('filter');
        $crop_infos = $request->get('crop_info');

        // Charge l'image à cropper
        $image = $this->getDoctrine()->getRepository('AropixelAdminBundle:Image')->find($image_id);

        // Pour chaque filtre passé, on recrope l'image chargée
        foreach ($crop_infos as $filter => $crop_info) {

            //
            $imageManager->saveCrop($image, $filter, $crop_info);

        }

        return new Response('Done', Response::HTTP_OK);

    }




    /**
     * Crop an Image.
     *
     * @Route("/title", name="image_title", options={"expose"=true}, methods={"POST"})
     */
    public function titleAction(Request $request)
    {
        //
        $image_id = $request->get('pk');
        $title = $request->get('value');

        //
        $em = $this->getDoctrine()->getManager();

        //
        $image = $this->getDoctrine()->getRepository('AropixelAdminBundle:Image')->find($image_id);
        $image->setTitre($title);
        $em->flush();


        //
        return new Response('Done', Response::HTTP_OK);
    }

}

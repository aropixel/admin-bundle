<?php
// src/Aropixel/AdminBundle/Services/Datatabler.php
namespace Aropixel\AdminBundle\Services;

use Aropixel\AdminBundle\Domain\Entity\Image;
use Aropixel\AdminBundle\Resolver\PathResolverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\PropertyAccess\PropertyAccess;


class ImageManager
{


    /** @var Container  */
    private $container;

    /** @var EntityManagerInterface  */
    private $em;

    /** @var PathResolverInterface  */
    private $pathResolver;

    /** @var FilterService  */
    private $filterService;




    public function __construct(Container $container, EntityManagerInterface $em, PathResolverInterface $pathResolver, FilterService $filterService)
    {
        $this->container = $container;
        $this->pathResolver  = $pathResolver;
        $this->filterService  = $filterService;
        $this->em  = $em;
    }




    /**
     * Récupère les filtres de crop attribué à aucune entité
     *
     * @return array
     */
    public function getOrphanFilters()
    {

        $editorFilers = $this->container->getParameter('aropixel_admin.editor_filter_sets');

        return $editorFilers;
    }




    /**
     * Récupère les filtres de crop pour une entité
     *
     * @param mixed $data
     * @param array $crops
     *
     * @return array
     */
    public function getCropFilters($data, $crops)
    {
//        if (is_null($data)) {
//            return [];
//        }

        //
        $accessor = PropertyAccess::createPropertyAccessor();
        try {
            $existingCrops = $accessor->getValue($data, 'crops');
            if (!is_null($existingCrops)) {

                foreach ($existingCrops as $crop) {
                    $filterName = $crop['filter'];
                    $cropinfo = $crop['crop'];
                    $existingCropsByName[$filterName] = $cropinfo;
                }

            }
        }
        catch (\Exception $e) {
            $existingCropsByName = [];
        }



        // Récupère les filtres existants et leurs labels (descriptions)
        $liip_filters = $this->container->getParameter('liip_imagine.filter_sets');

        // Tableau des filtres à retourner
        $filters = array();

        foreach ($liip_filters as $slug => $filter) {


            // Si ce filtre ne contient pas de miniature (juste un resize par exemple)
            // on ne le pred pas en compte
            if (!array_key_exists($slug, $crops))        continue;


            // Calcule le ratio du filtre
            $ratio = $filter['filters']['thumbnail']['size'][0] / $filter['filters']['thumbnail']['size'][1];


            // Construit les infos de retour
            $filter['crop'] = array_key_exists($slug, $existingCropsByName) ? $existingCropsByName[$slug] : "";
            $filter['ratio'] = $ratio;
            $filter['slug'] = $slug;
            $filter['name'] = $crops[$slug];

            //
            $filters[$crops[$slug]] = $filter;

        }

        return $filters;
    }




    /**
     * Récupère les filtres de crop pour une entité
     *
     * @param string $entity_name
     * @param AropixelAdminBundle:Image $image
     *
     * @return array
     */
    public function getEntityCropFilters($image, $imageClass)
    {
        //
        $imageClass = str_replace('Proxies\__CG__\\', '', $imageClass);

        // Récupère les filtres existants et leurs labels (descriptions)
        $all_filters = $this->container->getParameter('liip_imagine.filter_sets');
        $all_labels = $this->container->getParameter('aropixel_admin.filter_sets');

        //
        $existingCropsByName = array();
        if ($image && method_exists($image, "getCrops")) {
            $existingCrops = $image->getCrops();
            foreach ($existingCrops as $crop) {
                $existingCropsByName[$crop->getFilter()] = $crop;
            }
        }

        // Tableau des filtres à retourner
        $filters = array();

        //
        // On ne retourne que les filtres qui concerne le type d'entité passée
        if (array_key_exists($imageClass, $all_labels)) {

            foreach ($all_labels[$imageClass] as $slug => $name) {

                //
                $filter = $all_filters[$slug];

                // Si ce filtre ne contient pas de miniature (juste un resize par exemple)
                // on ne le pred pas en compte
                if (!isset($filter['filters']['thumbnail']))        continue;


                // Calcule le ratio du filtre
                $ratio = $filter['filters']['thumbnail']['size'][0] / $filter['filters']['thumbnail']['size'][1];


                // Construit les infos de retour
                $filter['crop'] = array_key_exists($slug, $existingCropsByName) ? $existingCropsByName[$slug]->getCrop() : "";
                $filter['ratio'] = $ratio;
                $filter['slug'] = $slug;
                $filter['name'] = $name;
                $filters[$name] = $filter;

            }

        }

        return $filters;
    }


//
//
//    /**
//     * Applique le crop en BDD et sur le fichier
//     *
//     * @param AropixelAdminBundle:Image $image
//     * @param string $filter
//     * @param string $crop_info
//     *
//     * @return void
//     */
//    public function saveCrop($image, $filter, $crop_info) {
//
//
//        // Sauvegarde les infos de crop pour cette image et ce filtre en base de données
//        $this->saveDatabaseCrop($image, $filter, $crop_info);
//
//        // Sauvegarde les infos de crop pour cette image et ce filtre en base de données
//        $this->saveFileCrop($image, $filter, $crop_info);
//
//
//    }
//



    /**
     * Applique le crop en BDD et sur le fichier
     *
     * @param Image $image
     * @param string $filter
     * @param string $crop_info
     *
     * @return string
     */
    public function editorResize($image, $width, $filter=null) {


        // Si aucun filtre mais une largeur
        // on récupère le filtre de largeur préconstruit par le bundle
        if ((is_null($filter) || !strlen($filter)) && $width) {
            $filter = 'editor_'.$width;
        }

        //
        if (!is_null($filter)) {
            $resourcePath = $this->filterService->getUrlOfFilteredImage($image->getWebPath(), $filter);
        }
        else {

            $size = getimagesize($this->pathResolver->getAbsolutePath(Image::UPLOAD_DIR, $image->getFilename()));

            // Runtime configuration
            $runtimeConfig = [
                'relative_resize' => [
                    'widen' => $size[0]
                ],
            ];

            $resourcePath = $this->filterService->getUrlOfFilteredImageWithRuntimeFilters(
                $image->getWebPath(),
                'auto',
                $runtimeConfig
            );

        }



        return $resourcePath;
    }




    /**
     * Applique le crop sur le fichier du filtre
     *
     * @param Image $image
     * @param string $filter
     * @param string $crop_info
     *
     * @return void
     */
    public function saveFileCrop($image, $filter, $crop_info) {

        //
        $t_crop = explode(',', $crop_info);

        // Récupération des services de liip imagine bundle
        $dataManager = $this->container->get('liip_imagine.data.manager');
        $filterManager = $this->container->get('liip_imagine.filter.manager');
        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        // Récupère la configuration du filtre
        $filter_actions = $filterManager->getFilterConfiguration()->get($filter);

        //
        $path = $this->pathResolver->getAbsolutePath(Image::UPLOAD_DIR, $image->getFilename());
        list($realWidth, $realHeight) = getimagesize($path);
        $ratio = 600 / $realWidth;

        // Ajoute le crop personnalisé avant toute action du filtre
        $crop_action = array(
            'crop' => array(
                'size' => array($t_crop[2] / $ratio, $t_crop[3] / $ratio),
                'start' => array($t_crop[0] / $ratio, $t_crop[1] / $ratio)
            ));
        $actions = array_merge($crop_action, $filter_actions['filters']);


        // Applique le filtre personnalisé et sauve l'image
        $binary = $dataManager->find($filter, $image->getWebPath());
        $filteredBinary = $filterManager->apply($binary, array(
            'filters' => $actions
        ));
        $cacheManager->store($filteredBinary, $image->getWebPath(), $filter);

    }

//
//
//    /**
//     * Sauvegarde le crop en base de données
//     *
//     * @param AropixelAdminBundle:Image $image
//     * @param string $filter
//     * @param string $crop_info
//     *
//     * @return void
//     */
//    protected function saveDatabaseCrop($image, $filter, $crop_info) {
//
//        // Récupère l'info de crop si elle existe déjà en base de données
//        $crop = $this->em->getRepository('AropixelAdminBundle:Crop')->findOneBy(array(
//            'image' => $image->getId(),
//            'filter' => $filter
//        ));
//
//        // Si elle n'existe pas on la crée
//        if (!$crop) {
//            $crop = new \Aropixel\AdminBundle\Entity\Crop();
//            $crop->setFilter($filter);
//            $crop->setImage($image);
//        }
//
//        // On attribue les nouvelles infos de crop
//        $crop->setCrop($crop_info);
//
//        // On sauve en base
//        $this->em->persist($crop);
//        $this->em->flush();
//
//    }
//


}

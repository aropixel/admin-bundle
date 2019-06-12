<?php

namespace Aropixel\AdminBundle\EventListener;

use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;


class DoFileCropListener
{

    private $dataManager;
    private $filterManager;
    private $cacheManager;

    /**
     */
    public function __construct(DataManager $dataManager, FilterManager $filterManager, CacheManager $cacheManager)
    {
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->doCrop($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->doCrop($args);
    }

    public function doCrop(LifecycleEventArgs $args)
    {

        //
        $entity = $args->getEntity();

        //
        $parentClass = get_parent_class($entity);
        if ($parentClass=='Aropixel\AdminBundle\Entity\Crop') {

            $image = $entity->getImage();
            $filter = $entity->getFilter();
            $crop_info = $entity->getCrop();
            if (!$crop_info)    return;

            //
            $t_crop = explode(',', $crop_info);

            // Récupération des services de liip imagine bundle
            $dataManager = $this->dataManager;
            $filterManager = $this->filterManager;
            $cacheManager = $this->cacheManager;

            // Récupère la configuration du filtre
            $filter_actions = $filterManager->getFilterConfiguration()->get($filter);

            //
            $path = $image->getAbsolutePath();
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


    }

}

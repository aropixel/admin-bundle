<?php
namespace Aropixel\AdminBundle\Twig;

use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Services\ImageManager;
use Aropixel\AdminBundle\Services\Seo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;



class AropixelExtension extends AbstractExtension
{
    /** @var RequestStack  */
    private $requestStack;

    /** @var RouterInterface  */
    private $router;

    /** @var ImageManager  */
    private $imageManager;

    /** @var Seo  */
    private $seo;

    /** @var bool  */
    private $loadLibrary;

    /** @var bool  */
    private $loadFilesLibrary;


    public function __construct(RequestStack $requestStack, RouterInterface $router, EntityManagerInterface $em, ImageManager $imageManager, Seo $seo)
    {
        $this->imageManager = $imageManager;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->em = $em;
        $this->seo = $seo;
        $this->loadLibrary = false;
        $this->loadFilesLibrary = false;
    }

    public function getGlobals()
    {
        return array(
            'loadLibrary' => $this->loadLibrary,
            'loadFilesLibrary' => $this->loadFilesLibrary,
        );
    }

    public function getFilters()
    {
        return array(
            'datetime' => new TwigFilter('datetime', array($this, 'intl_date')),
            'intl_date' => new TwigFilter('intl_date', array($this, 'intl_date')),
            'crop_filters' => new TwigFilter('crop_filters', array($this, 'cropFilters')),
            'entity_crop_filters' => new TwigFilter('entity_crop_filters', array($this, 'entityCropFilters')),
            'class_name' => new TwigFilter('class_name', array($this, 'getClassFromCategory')),
            'seo' => new TwigFilter('seo', array($this, 'getSeo')),
            'ucfirst' => new TwigFilter('ucfirst', array($this, 'myUcfirst')),
            'filename_web_path' => new TwigFilter('filename_web_path', array($this, 'getFileNameWebPath')),
        );
    }


    public function getFunctions()
    {
        return array(
            'route_exists' => new TwigFunction('route_exists', array($this, 'routeExists')),
            'get_baseroute' => new TwigFunction('get_baseroute', array($this, 'getBaseRoute')),
            'get_image_editor_route' => new TwigFunction('get_image_editor_route', array($this, 'getImageEditorRoute')),
            'get_class' => new TwigFunction('get_class', array($this, 'getClass')),
            'load_library' => new TwigFunction('load_library', array($this, 'setLoadLibrary')),
            'load_files_library' => new TwigFunction('load_files_library', array($this, 'setLoadFilesLibrary')),
            'get_short_class' => new TwigFunction('get_short_class', array($this, 'getShortClass')),
            'orphan_filters' => new TwigFunction('orphan_filters', array($this, 'getOrphanFilters')),
        );
    }

    public function getName()
    {
        return 'getclass';
    }


    function getBaseRoute()
    {
        $request = $this->requestStack->getCurrentRequest();
        $routeName = $request->get('_route');
        $i = strrpos($routeName, '_');
        $baseRoute = substr($routeName, 0, $i);
        return $baseRoute;
    }


    function getImageEditorRoute()
    {
        $path = $this->router->generate('image_editor');
        return $path;
    }


    public function getClass($object)
    {
        return $object && is_object($object) ? (new \ReflectionClass($object))->getName() : "";
    }


    public function getShortClass($object)
    {
        return $object && is_object($object) ? (new \ReflectionClass($object))->getShortName() : "";
    }


    public function setLoadLibrary($load=null)
    {
        if (!is_null($load)) {
            $this->loadLibrary = $load;
        }
        else {
            return $this->loadLibrary;
        }
    }


    public function setLoadFilesLibrary($load=null)
    {
        if (!is_null($load)) {
            $this->loadFilesLibrary = $load;
        }
        else {
            return $this->loadFilesLibrary;
        }
    }

    /**
     * récupère le chemin de l'image uploadée en parametre pour une marque blanche
     */
    public function getFileNameWebPath($filename)
    {
        return Image::getFileNameWebPath($filename);
    }


    public function myUcfirst($text)
    {
        return ucfirst($text);
    }


    public function getClassFromCategory($category)
    {
        return substr($category, strrpos($category, '\\') + 1);
    }


    /**
     * @param mixed $entity         Objet dans lequel recupérer les infos
     * @param string $seoField      Balise SEO à gérer (title, description, ou keywords)
     * @param string $defaultField  Champs de l'objet à utiliser pour générer le contenu (par défaut, égal à $seoField)
     * @param string $defaultText   Valeur par défaut si rien n'est trouvé
     * @param string $appendText    Valeur à ajouter par défaut au texte généré
     * @return string
     */
    public function getSeo($entity, $seoField, $defaultField="", $defaultText="", $appendText="")
    {
        //
        if (!$defaultField || !strlen($defaultField)) {
            $defaultField = $seoField=='keywords' ? 'description' : $seoField;
        }

        //
        $accessor = PropertyAccess::createPropertyAccessor();


        // Par défaut on cherche dans les champs getMeta[NOM DU CHAMPS]
        try {
            $seoText = $accessor->getValue($entity, $seoField);
        }
        catch (NoSuchPropertyException $e) {
            $seoText = "";
        }


        // Si non trouvé, on cherche dans les champs get[NOM DU CHAMPS]
        if (!strlen($seoText)) {

            // Par défaut on cherche dans les champs getMeta[NOM DU CHAMPS]
            try {
                $seoText = $accessor->getValue($entity, $defaultField);
            }
            catch (NoSuchPropertyException $e) {
                $seoText = "";
            }

            if (strlen($seoText)) {
                $seoText.= $appendText;
            }
        }


//        // Si non trouvé, on cherche dans les champs getMeta[NOM DU CHAMPS] de la traduction
//        if (!strlen($seoText) && method_exists($entity, 'translate') && method_exists($entity->translate(), $seoMethod)) {
//            $seoText = $entity->translate()->{$seoMethod}();
//        }
//
//        // Si non trouvé, on cherche dans les champs get[NOM DU CHAMPS] de la traduction
//        if (!strlen($seoText) && method_exists($entity, 'translate') && method_exists($entity->translate(), $dftMethod)) {
//            $seoText = $entity->translate()->{$dftMethod}();
//            if (strlen($seoText)) {
//                $seoText.= $appendText;
//            }
//        }


        //
        if ($seoField == 'title') {
            return $this->seo->text($seoText ? $seoText : $defaultText, 70);
        }
        elseif ($seoField == 'description') {
            return $this->seo->text($seoText ? $seoText : $defaultText);
        }
        elseif ($seoField == 'keywords') {
            return $this->seo->keywords($seoText ? $seoText : $defaultText, 15, $seoText ? true : false);
        }

        return $seoText;
    }

    function routeExists($name)
    {
        return (null === $this->router->getRouteCollection()->get($name)) ? false : true;
    }


    public function intl_date($d, $format = "%B %e", $lang="fr_FR")
    {
        $formatter = new \IntlDateFormatter($lang, \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
        $formatter->setPattern($format);
        return $formatter->format($d);
    }


    public function cropFilters($data, $crops)
    {
        //
        $filters = $this->imageManager->getCropFilters($data, $crops);

        //
        return $filters;
    }


    public function entityCropFilters($image, $imageClass)
    {
        //
        $filters = $this->imageManager->getEntityCropFilters($image, $imageClass);

        //
        return $filters;
    }


    public function getOrphanFilters()
    {
        //
        $filters = $this->imageManager->getOrphanFilters();

        //
        return $filters;
    }


}

<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 06/11/2019 à 16:41
 */

namespace Aropixel\AdminBundle\Twig;


use Aropixel\AdminBundle\Entity\AttachImage;
use Aropixel\AdminBundle\Entity\Image;
use Aropixel\AdminBundle\Image\PathResolver;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ImageExtension extends AbstractExtension
{

    /** @var RequestStack */
    private $requestStack;

    /** @var KernelInterface */
    private $kernel;

    /** @var ParameterBagInterface */
    private $parameterBag;

    /** @var PathResolver */
    private $pathResolver;

    /** @var CacheManager */
    private $cacheManager;

    /** @var FilterService  */
    private $filterService;

    /**
     * ImageExtension constructor.
     * @param RequestStack $requestStack
     * @param KernelInterface $kernel
     * @param ParameterBagInterface $parameterBag
     * @param PathResolver $pathResolver
     * @param CacheManager $cacheManager
     * @param FilterService $filterService
     */
    public function __construct(RequestStack $requestStack, KernelInterface $kernel, ParameterBagInterface $parameterBag, PathResolver $pathResolver, CacheManager $cacheManager, FilterService $filterService)
    {
        $this->requestStack = $requestStack;
        $this->kernel = $kernel;
        $this->parameterBag = $parameterBag;
        $this->pathResolver = $pathResolver;
        $this->cacheManager = $cacheManager;
        $this->filterService = $filterService;
    }


    public function getFilters()
    {
        return array(
            new TwigFilter('aropixel_imagine_filter', array($this, 'customImagineFilter')),
        );
    }


    public function customImagineFilter($image, $filter, array $config = [], $resolver = null)
    {
        $path = $image;
        $isImage = ($image instanceof AttachImage);


        if ($isImage) {
            /** @var AttachImage $image */
            $path = $this->pathResolver->fileExists($image->getFilename()) ? $image->getWebPath() : null;
        }
        else {
            $path = Image::getFileNameWebPath($image);
        }
//
//        if (!is_string($image) || !file_exists($image)) {
//            $path = null;
//        }

        if (is_null($path)) {

            $filterSets = $this->parameterBag->get('liip_imagine.filter_sets');
            if (!array_key_exists($filter, $filterSets)) {
                throw new \Exception(sprintf('Le filtre "%s" n\'existe pas', $filter));
            }

            $width = $height = 200;
            $config = $filterSets[$filter]['filters'];

            if (array_key_exists('thumbnail', $config)) {
                $width = $config['thumbnail']['size'][0];
                $height = $config['thumbnail']['size'][1];
            }
            elseif (array_key_exists('scale', $config)) {
                $width = $config['scale']['dim'][0];
                $height = $config['scale']['dim'][1];
            }


            $filter = 'fallback_pixel';
            $path = 'pixel.png';

            // Runtime configuration
            $runtimeConfig = [
                'scale' => [
                    'dim' => array($width, $height),
                ],
                'background' => [
                    'size' => array($width, $height),
                    'position' => 'center',
                    'color' => '#d5d5d5',
                ]
            ];

            $resourcePath = $this->filterService->getUrlOfFilteredImageWithRuntimeFilters(
                $path,
                $filter,
                $runtimeConfig
            );

            return $resourcePath;
        }

        return $this->cacheManager->getBrowserPath(parse_url($path, PHP_URL_PATH), $filter, [], null);
    }

}

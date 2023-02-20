<?php
/**
 * Créé par Aropixel @2019.
 * Par: Joël Gomez Caballe
 * Date: 06/11/2019 à 16:41
 */

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Twig;


use Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\AttachImage;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ImageExtension extends AbstractExtension
{

    private CacheManager $cacheManager;
    private FilterService $filterService;
    private ParameterBagInterface $parameterBag;
    private PathResolverInterface $pathResolver;

    /**
     * @param CacheManager $cacheManager
     * @param FilterService $filterService
     * @param ParameterBagInterface $parameterBag
     * @param \Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface $pathResolver
     */
    public function __construct(CacheManager $cacheManager, FilterService $filterService, ParameterBagInterface $parameterBag, PathResolverInterface $pathResolver)
    {
        $this->cacheManager = $cacheManager;
        $this->filterService = $filterService;
        $this->parameterBag = $parameterBag;
        $this->pathResolver = $pathResolver;
    }


    public function getFilters()
    {
        return array(
            new TwigFilter('aropixel_imagine_filter', array($this, 'customImagineFilter')),
        );
    }


    public function customImagineFilter(?string $webPath, string $filter, array $config = [], $resolver = null)
    {

        /** @var AttachImage $image */

        if (is_null($webPath) || !$this->pathResolver->privateFileExists($webPath)) {

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
            $webPath = 'pixel.png';

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
                $webPath,
                $filter,
                $runtimeConfig
            );

            return $resourcePath;
        }

        return $this->cacheManager->getBrowserPath(parse_url($webPath, PHP_URL_PATH), $filter, [], null);
    }

}

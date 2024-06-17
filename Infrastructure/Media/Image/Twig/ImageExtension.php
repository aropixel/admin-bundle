<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Twig;

use Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\AttachedImage;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ImageExtension extends AbstractExtension
{
    /**
     * @param \AdminBundle\Domain\Media\Resolver\PathResolverInterface $pathResolver
     */
    public function __construct(
        private readonly CacheManager $cacheManager,
        private readonly FilterService $filterService,
        private readonly ParameterBagInterface $parameterBag,
        private readonly PathResolverInterface $pathResolver
    ) {
    }

    public function getFilters()
    {
        return [new TwigFilter('aropixel_imagine_filter', $this->customImagineFilter(...))];
    }

    public function customImagineFilter(?string $webPath, string $filter, array $config = [], $resolver = null)
    {
        /* @var AttachedImage $image */

        if (null === $webPath || !$this->pathResolver->privateFileExists($webPath)) {
            $filterSets = $this->parameterBag->get('liip_imagine.filter_sets');
            if (!\array_key_exists($filter, $filterSets)) {
                throw new \Exception(sprintf('Le filtre "%s" n\'existe pas', $filter));
            }

            $width = $height = 200;
            $config = $filterSets[$filter]['filters'];

            if (\array_key_exists('thumbnail', $config)) {
                $width = $config['thumbnail']['size'][0];
                $height = $config['thumbnail']['size'][1];
            } elseif (\array_key_exists('scale', $config)) {
                $width = $config['scale']['dim'][0];
                $height = $config['scale']['dim'][1];
            }

            $filter = 'fallback_pixel';
            $webPath = 'pixel.png';

            // Runtime configuration
            $runtimeConfig = [
                'scale' => [
                    'dim' => [$width, $height],
                ],
                'background' => [
                    'size' => [$width, $height],
                    'position' => 'center',
                    'color' => '#d5d5d5',
                ],
            ];

            return $this->filterService->getUrlOfFilteredImageWithRuntimeFilters(
                $webPath,
                $filter,
                $runtimeConfig
            );
        }

        return $this->cacheManager->getBrowserPath(parse_url($webPath, \PHP_URL_PATH), $filter, [], null);
    }
}

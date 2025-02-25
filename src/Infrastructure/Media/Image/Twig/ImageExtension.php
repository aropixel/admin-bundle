<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Twig;

use Aropixel\AdminBundle\Domain\Media\Resolver\PathResolverInterface;
use Aropixel\AdminBundle\Entity\AttachedImage;
use Aropixel\AdminBundle\Entity\ImageInterface;
use League\Flysystem\FilesystemOperator;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ImageExtension extends AbstractExtension
{
    public function __construct(
        private readonly CacheManager $cacheManager,
        private readonly FilesystemOperator $privateStorage,
        private readonly FilterService $filterService,
        private readonly ParameterBagInterface $parameterBag,
        private readonly PathResolverInterface $pathResolver,
        private bool $isLibraryEnabled = false,
    ) {
    }

    public function getFilters()
    {
        return [new TwigFilter('aropixel_imagine_filter', $this->customImagineFilter(...))];
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('enable_image_library_modal', $this->enableImageLibraryModal(...)), new TwigFunction('is_image_library_modal_enabled', $this->isImageLibraryModalEnabled(...))];
    }

    public function enableImageLibraryModal(): void
    {
        $this->isLibraryEnabled = true;
    }

    public function isImageLibraryModalEnabled(): bool
    {
        return $this->isLibraryEnabled;
    }

    public function customImagineFilter(?ImageInterface $image, string $filter, array $config = [], $resolver = null)
    {
        /* @var AttachedImage $image */
        try {
            $shouldProducePlaceholder =
                null === $image ||
                !$this->privateStorage->fileExists($this->pathResolver->getImagePath($image))
            ;

        }
        catch (\Throwable) {
            $shouldProducePlaceholder = true;
        }

        if ($shouldProducePlaceholder) {
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

        return $this->cacheManager->getBrowserPath(parse_url($this->pathResolver->getImagePath($image), \PHP_URL_PATH), $filter, [], null);
    }
}

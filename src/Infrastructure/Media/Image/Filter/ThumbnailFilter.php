<?php

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Filter;

use Imagine\Filter\Basic\Crop;
use Imagine\Filter\Basic\Resize;
use Imagine\Image\Box;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThumbnailFilter implements LoaderInterface
{
    /**
     * @param array{'width'?: int, 'height'?: int} $options
     * @return ImageInterface
     */
    public function load(ImageInterface $image, array $options = []): ImageInterface
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setRequired(['width', 'height']);
        $options = $optionsResolver->resolve($options);

        // get the original image size and create a crop box
        $size = $image->getSize();
        $box = new Box($options['width'], $options['height']);

        // determine scale
        $size = $this->fillBox($size, $box);

        // define filters
        $resize = new Resize($size);
        $origin = new Point(
            (int)ceil(($size->getWidth() - $box->getWidth()) / 2),
            (int)ceil(($size->getHeight() - $box->getHeight()) / 2)
        );
        $crop = new Crop($origin, $box);

        // apply filters to image
        $image = $resize->apply($image);

        return $crop->apply($image);
    }

    private function fillBox(BoxInterface $sourceBox, BoxInterface $targetBox): BoxInterface
    {
        $sourceAspect = $sourceBox->getWidth() / $sourceBox->getHeight();
        $targetAspect = $targetBox->getWidth() / $targetBox->getHeight();

        if ($sourceAspect > $targetAspect) {
            return $sourceBox->heighten($targetBox->getHeight());
        }

        return $sourceBox->widen($targetBox->getWidth());
    }
}

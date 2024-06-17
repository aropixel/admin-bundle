<?php

namespace Aropixel\AdminBundle\Twig;

use Aropixel\AdminBundle\Domain\Seo\Seo;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AropixelExtension extends AbstractExtension
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router
    ) {
    }

    public function getFilters()
    {
        return ['datetime' => new TwigFilter('datetime', $this->intl_date(...)), 'intl_date' => new TwigFilter('intl_date', $this->intl_date(...)), 'seo' => new TwigFilter('seo', $this->getSeo(...)), 'ucfirst' => new TwigFilter('ucfirst', $this->myUcfirst(...))];
    }

    public function getFunctions()
    {
        return ['route_exists' => new TwigFunction('route_exists', $this->routeExists(...)), 'get_baseroute' => new TwigFunction('get_baseroute', $this->getBaseRoute(...)), 'get_image_editor_route' => new TwigFunction('get_image_editor_route', $this->getImageEditorRoute(...)), 'get_class' => new TwigFunction('get_class', $this->getClass(...))];
    }

    public function getName()
    {
        return 'getclass';
    }

    public function getBaseRoute()
    {
        $request = $this->requestStack->getCurrentRequest();
        $routeName = $request->get('_route');
        $i = mb_strrpos((string) $routeName, '_');

        return mb_substr((string) $routeName, 0, $i);
    }

    public function getImageEditorRoute()
    {
        return $this->router->generate('image_editor');
    }

    public function getClass($object)
    {
        return $object && \is_object($object) ? (new \ReflectionClass($object))->getName() : '';
    }

    public function myUcfirst($text)
    {
        return ucfirst((string) $text);
    }

    /**
     * @param string $seoField     Balise SEO à gérer (title, description, ou keywords)
     * @param string $defaultField Champs de l'objet à utiliser pour générer le contenu (par défaut, égal à $seoField)
     * @param string $defaultText  Valeur par défaut si rien n'est trouvé
     * @param string $appendText   Valeur à ajouter par défaut au texte généré
     *
     * @return string
     */
    public function getSeo(mixed $entity, $seoField, $defaultField = '', $defaultText = '', $appendText = '')
    {
        if (!$defaultField || !mb_strlen($defaultField)) {
            $defaultField = 'keywords' == $seoField ? 'description' : $seoField;
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        // Par défaut on cherche dans les champs getMeta[NOM DU CHAMPS]
        try {
            $seoText = $accessor->getValue($entity, $seoField);
        } catch (NoSuchPropertyException) {
            $seoText = '';
        }

        // Si non trouvé, on cherche dans les champs get[NOM DU CHAMPS]
        if (!mb_strlen((string) $seoText)) {
            // Par défaut on cherche dans les champs getMeta[NOM DU CHAMPS]
            try {
                $seoText = $accessor->getValue($entity, $defaultField);
            } catch (NoSuchPropertyException) {
                $seoText = '';
            }

            if (mb_strlen((string) $seoText)) {
                $seoText .= $appendText;
            }
        }

        if ('title' == $seoField) {
            return Seo::text($seoText ?: $defaultText, 70);
        }
        if ('description' == $seoField) {
            return Seo::text($seoText ?: $defaultText);
        }
        if ('keywords' == $seoField) {
            return Seo::keywords($seoText ?: $defaultText, 15, (bool) $seoText);
        }

        return $seoText;
    }

    public function routeExists($name)
    {
        return !(null === $this->router->getRouteCollection()->get($name));
    }

    public function intl_date($d, $format = '%B %e', $lang = 'fr_FR')
    {
        $formatter = new \IntlDateFormatter($lang, \IntlDateFormatter::NONE, \IntlDateFormatter::NONE);
        $formatter->setPattern($format);

        return $formatter->format($d);
    }
}

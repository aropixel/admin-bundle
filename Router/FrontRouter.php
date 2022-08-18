<?php


namespace Aropixel\AdminBundle\Router;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;


class FrontRouter implements WarmableInterface, ServiceSubscriberInterface, RouterInterface, RequestMatcherInterface
{

    /**
     * @var Router original
     */
    private $router;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(Router $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->router->getRouteCollection();
    }

    public function warmUp(string $cacheDir): array
    {
        return $this->router->warmUp($cacheDir);
    }

    public static function getSubscribedServices() : array
    {
        return [
            'routing.loader' => LoaderInterface::class,
        ];
    }

    public function setContext(RequestContext $context)
    {
        return $this->router->setContext($context);
    }

    public function getContext(): RequestContext
    {
        return $this->router->getContext();
    }

    public function matchRequest(Request $request): array
    {
        return $this->router->matchRequest($request);
    }

    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        if($this->isNotProfilerRoute($name) &&
            $this->isNotAdminRoute($name)
        ){

            $request = $this->requestStack->getCurrentRequest();

            if ($request && $request->attributes->has('_locale')) {
                $locale = $request->attributes->get('_locale');
                $parameters['_locale'] = $locale;
            }
        }

        return $this->router->generate($name, $parameters, $referenceType);
    }

    private function isNotProfilerRoute(string $name): bool
    {
        return substr($name, 0, 1) !== '_';
    }

    private function isNotAdminRoute(string $name): bool
    {
        return substr($name, 0, 15) !== 'aropixel_admin';
    }

    public function match(string $pathinfo): array
    {
        return $this->router->match($pathinfo);
    }
}

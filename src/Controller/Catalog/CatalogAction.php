<?php

namespace Aropixel\AdminBundle\Controller\Catalog;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Component catalogue — the living documentation of the design system (design-system.md §14).
 *
 * Renders every component in all its states, on the *real* bundle CSS (the template extends
 * the admin base), so it cannot drift from what the admin actually looks like. Dev only:
 * it is a build-time reference, never shipped to a production admin.
 */
class CatalogAction extends AbstractController
{
    public function __construct(
        private readonly string $environment,
    ) {
    }

    public function __invoke(): Response
    {
        if ('dev' !== $this->environment) {
            throw new NotFoundHttpException();
        }

        return $this->render('@AropixelAdmin/catalog/index.html.twig');
    }
}

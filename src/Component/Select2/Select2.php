<?php

namespace Aropixel\AdminBundle\Component\Select2;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class Select2 implements Select2Interface
{
    private ?Select2DataProviderInterface $provider = null;
    private string $searchTerm = '';
    private int $page = 1;
    private int $itemsPerPage = 20;

    /** @var callable|null */
    private $filterCallback = null;

    /**
     * @param iterable<Select2DataProviderInterface> $providers
     */
    public function __construct(
        private readonly RequestStack $requestStack,
        #[TaggedIterator('aropixel.select2_provider')]
        private readonly iterable $providers
    ) {
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $this->searchTerm = $request->query->get('q', '');
            $this->page = max(1, (int)$request->query->get('page', '1'));
        }
    }

    public function withProvider(string $alias): self
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($alias)) {
                $this->provider = $provider;
                return $this;
            }
        }

        throw new \InvalidArgumentException(sprintf("Aucun fournisseur Select2 trouvé pour l'alias '%s'", $alias));
    }

    public function filter(callable $callback): self
    {
        $this->filterCallback = $callback;
        return $this;
    }

    public function render(callable $transformer): Response
    {
        if (!$this->provider) {
            throw new \LogicException("Aucun fournisseur n'a été défini. Appelez withProvider() d'abord.");
        }

        $qb = $this->provider->getQueryBuilder($this->searchTerm);

        // Appliquer les filtres personnalisés si définis
        if ($this->filterCallback) {
            ($this->filterCallback)($qb);
        }

        // Calculer le total (avant pagination)
        $totalCount = $this->count($qb, $this->provider->getRootAlias());

        // Récupérer les items paginés
        $offset = ($this->page - 1) * $this->itemsPerPage;
        $items = $qb
            ->setFirstResult($offset)
            ->setMaxResults($this->itemsPerPage)
            ->getQuery()
            ->getResult();

        // Transformer les entités en tableau de résultats
        $results = array_map($transformer, $items);

        // Retourner la JsonResponse formatée pour Select2
        return new JsonResponse([
            'results' => $results,
            'pagination' => [
                'more' => ($this->page * $this->itemsPerPage) < $totalCount
            ],
            'total_count' => $totalCount
        ]);
    }

    private function count(QueryBuilder $qb, string $alias): int
    {
        $qbCount = clone $qb;
        return (int) $qbCount
            ->select($qbCount->expr()->count($alias))
            ->setFirstResult(0)
            ->setMaxResults(null)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

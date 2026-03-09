<?php

namespace Aropixel\AdminBundle\Component\Select2;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class Select2 implements Select2Interface
{
    private ?Select2DataProviderInterface $provider = null;
    private ?string $entityClassName = null;
    private array $searchFields = [];
    private string $searchTerm = '';
    private int $page = 1;
    private int $itemsPerPage = 20;

    /** @var callable|null */
    private $filterCallback;

    /**
     * @param iterable<Select2DataProviderInterface> $providers
     */
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $em,
        #[TaggedIterator('aropixel.select2_provider')]
        private readonly iterable $providers
    ) {
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $this->searchTerm = $request->query->get('q', '');
            $this->page = max(1, (int) $request->query->get('page', '1'));
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

    public function withEntity(string $className): self
    {
        $this->entityClassName = $className;

        return $this;
    }

    public function searchIn(array $fields): self
    {
        $this->searchFields = $fields;

        return $this;
    }

    public function filter(callable $callback): self
    {
        $this->filterCallback = $callback;

        return $this;
    }

    public function render(callable $transformer = null): Response
    {
        if (!$this->provider && !$this->entityClassName) {
            throw new \LogicException("Aucun fournisseur ou entité n'a été défini. Appelez withProvider() ou withEntity() d'abord.");
        }

        // Si on a un provider, on l'utilise pour récupérer le QueryBuilder de base
        if ($this->provider) {
            $qb = $this->provider->getQueryBuilder($this->searchTerm);
            $rootAlias = $this->provider->getRootAlias();
        } else {
            // Sinon on crée un QueryBuilder par défaut à partir de l'entité
            $rootAlias = 'e';
            $qb = $this->em->getRepository($this->entityClassName)->createQueryBuilder($rootAlias);
        }

        // Gestion de la recherche automatique si searchIn est utilisé
        if (mb_strlen($this->searchTerm) && count($this->searchFields)) {
            $orX = $qb->expr()->orX();
            foreach ($this->searchFields as $field) {
                $orX->add($qb->expr()->like($rootAlias . '.' . $field, ':q'));
            }
            $qb->andWhere($orX);
            $qb->setParameter('q', '%' . $this->searchTerm . '%');
        }

        // Appliquer les filtres personnalisés si définis
        if ($this->filterCallback) {
            ($this->filterCallback)($qb);
        }

        // Calculer le total (avant pagination)
        $totalCount = $this->count($qb, $rootAlias);

        // Récupérer les items paginés
        $offset = ($this->page - 1) * $this->itemsPerPage;
        $items = $qb
            ->setFirstResult($offset)
            ->setMaxResults($this->itemsPerPage)
            ->getQuery()
            ->getResult()
        ;

        // Transformer les entités en tableau de résultats
        $results = array_map(function($item) use ($transformer) {

            // Si un transformer est fourni, on l'appelle
            if ($transformer) {

                $result = $transformer($item);

                // Si le transformer retourne déjà un tableau, on s'assure que full_name est présent
                if (is_array($result)) {

                    // Si c'est un tableau indexé (ex: [$id, $full_name])
                    if (isset($result[0]) && isset($result[1])) {
                        return [
                            'id' => $result[0],
                            'full_name' => $result[1],
                        ];
                    }

                    // Si text est présent (ancien format), on le mappe vers full_name
                    if (!isset($result['full_name']) && isset($result['text'])) {
                        $result['full_name'] = $result['text'];
                        unset($result['text']);
                    }

                    // Si id n'est pas présent, on met l'id par défaut
                    if (!isset($result['id'])) {
                        $result['id'] = $item->getId();
                    }

                    return $result;
                }

                // Si le transformer retourne une valeur simple, on l'utilise pour full_name
                return [
                    'id' => $item->getId(),
                    'full_name' => (string)$result,
                ];
            }

            // Par défaut, on utilise getId() et __toString()
            return [
                'id' => $item->getId(),
                'full_name' => (string)$item,
            ];

        }, $items);

        // Retourner la JsonResponse formatée pour Select2
        return new JsonResponse([
            'results' => $results,
            'pagination' => [
                'more' => ($this->page * $this->itemsPerPage) < $totalCount,
            ],
            'total_count' => $totalCount,
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
            ->getSingleScalarResult()
        ;
    }
}

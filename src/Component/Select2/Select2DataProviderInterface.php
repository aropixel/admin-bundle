<?php

namespace Aropixel\AdminBundle\Component\Select2;

use Doctrine\ORM\QueryBuilder;

interface Select2DataProviderInterface
{
    /**
     * Retourne le QueryBuilder de base pour la recherche.
     */
    public function getQueryBuilder(string $searchTerm): QueryBuilder;

    /**
     * Retourne l'alias racine utilisé dans le QueryBuilder.
     */
    public function getRootAlias(): string;

    /**
     * Indique si ce fournisseur supporte un alias/nom spécifique (ex: "customer", "product").
     */
    public function supports(string $alias): bool;
}

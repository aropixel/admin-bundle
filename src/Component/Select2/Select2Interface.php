<?php

namespace Aropixel\AdminBundle\Component\Select2;

use Symfony\Component\HttpFoundation\Response;

interface Select2Interface
{
    /**
     * Définit le fournisseur de données à utiliser par son alias.
     */
    public function withProvider(string $alias): self;

    /**
     * Définit la classe de l'entité à utiliser (si pas de fournisseur).
     */
    public function withEntity(string $className): self;

    /**
     * Définit les champs sur lesquels la recherche textuelle doit s'effectuer.
     */
    public function searchIn(array $fields): self;

    /**
     * Permet d'ajouter des filtres personnalisés via une closure.
     */
    public function filter(callable $callback): self;

    /**
     * Exécute la requête, applique les filtres et retourne la réponse JSON.
     * Le $transformer permet de formater chaque ligne.
     */
    public function render(callable $transformer): Response;
}

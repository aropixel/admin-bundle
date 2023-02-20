<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 20/02/2023 à 16:17
 */

namespace Aropixel\AdminBundle\Infrastructure\Media\Image\Library;

use Aropixel\AdminBundle\Infrastructure\DataTable\Repository\Doctrine\DefaultDataTableRepository;

class DataTableRepository extends DefaultDataTableRepository
{
    protected $repositoryMethodName = 'getCategoryQueryDataTable';

}

<?php
/**
 * Créé par Aropixel @2023.
 * Par: Joël Gomez Caballe
 * Date: 20/02/2023 à 15:23
 */

namespace Aropixel\AdminBundle\Infrastructure\DataTable\Context;

use Aropixel\AdminBundle\Domain\DataTable\DataTableContext;
use Aropixel\AdminBundle\Domain\DataTable\DataTableContextFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class DataTableContextFactory implements DataTableContextFactoryInterface
{
    private RequestStack $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function create(): DataTableContext
    {
        $request = $this->requestStack->getCurrentRequest();
        $start   = $request->query->get('start', 0);
        $length  = $request->query->get('length', 50);
        $draw    = $request->query->get('draw', 0);

        $all = $request->query->all();
        $order = array_key_exists('order', $all) ? $all['order'] : [];
        $orderColumn = isset($order[0]) ? $order[0]['column'] : 0;
        $orderDirection = isset($order[0]) ? $order[0]['dir'] : 'ASC';

        $search = '';
        if (array_key_exists('search', $all)) {
            $searchArray = $all['search'];
            $search = isset($searchArray['value']) ? $searchArray['value'] : '';
        }

        return new DataTableContext($search, $draw, $start, $length, $orderColumn, $orderDirection);
    }

}
<?php
namespace Aropixel\AdminBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class Datatabler
 * @package Aropixel\AdminBundle\Services
 */
class Datatabler
{

    protected RequestStack $requestStack;
    protected EntityManagerInterface $em;
    private QueryBuilder $qb;
    private array $params;
    private string $tableAs;
    private array $columns;


    /**
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $em
     */
    public function __construct(RequestStack $requestStack, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->columns = [];

        $request = $this->requestStack->getCurrentRequest();
        $all = $request->query->all();

        $draw    = $request->query->get('draw', 0);
        $start   = $request->query->get('start', 0);
        $length  = $request->query->get('length', 50);
        $order = array_key_exists('order', $all) ? $all['order'] : [];


        $params = [];
        $params['draw'] = $draw;
        $params['length'] = $length;
        $params['start'] = $start;
        $params['sort_col'] = isset($order[0]) ? $order[0]['column'] : 0;
        $params['sort_dir'] = isset($order[0]) ? $order[0]['dir'] : 'ASC';
        if (array_key_exists('search', $all)) {
            $search = $all['search'];
            $params['search'] = isset($search['value']) ? $search['value'] : '';
        }
        $this->params = $params;
    }


    public function setColumns(array $dataTableFields) : self
    {
        $this->columns = $dataTableFields;
        return $this;
    }


    public function setRepository(string $repoName, array $dataTableFields, string $repositoryMethod='getQueryDataTable')
    {

        if (strpos($repoName, ':') !== false) {

            list($bundle, $entity) = explode(':', $repoName);
            $this->tableAs = strtolower(substr($entity, 0, 1));

        }
        else {

            $reflection = new \ReflectionClass($repoName);
            $shortName = $reflection->getShortName();
            $this->tableAs = strtolower(substr($shortName, 0, 1));

        }

        $this->qb = $this->em->getRepository($repoName)->{$repositoryMethod}($this->params);

        $this->columns = $dataTableFields;
        $this->setOrder($dataTableFields);

        return $this;
    }


    public function setQueryBuilder(QueryBuilder $qb, string $letter)
    {
        $this->tableAs = $letter;
        $this->qb = $qb;
    }


    public function getQueryBuilder() : QueryBuilder
    {
        return $this->qb;
    }

    public function getLength() : mixed
    {
        return $this->params['length'];
    }


    public function getStart() : mixed
    {
        return $this->params['start'];
    }

    public function getPage() : mixed
    {
        return $this->params['draw'];
    }

    public function getSearch() : mixed
    {
        return $this->params['search'];
    }


    public function getOrderField() : mixed
    {
        if (array_key_exists($this->params['sort_col'], $this->columns) && array_key_exists('field', $this->columns[$this->params['sort_col']])) {
            return $this->columns[$this->params['sort_col']]['field'];
        }
    }

    public function getOrderDirection() : mixed
    {
        if (array_key_exists($this->params['sort_col'], $this->columns) && array_key_exists('field', $this->columns[$this->params['sort_col']])) {
            return $this->params['sort_dir'];
        }
    }

    public function setOrder(array $dataTableFields) : mixed
    {
        if (array_key_exists($this->params['sort_col'], $dataTableFields) && array_key_exists('field', $dataTableFields[$this->params['sort_col']])) {
            $this->qb->orderBy($dataTableFields[$this->params['sort_col']]['field'], $this->params['sort_dir']);
        }
    }

    public function getItems() : mixed
    {
        $query = $this->qb->getQuery();

        if ($this->params['start']) {
            $query->setFirstResult( $this->params['start'] );
        }

        if ($this->params['length']) {
            $query->setMaxResults( $this->params['length'] );
        }

        return $query->getResult();
    }


    public function getResponse(Response $response, ?int $count=null) : Response
    {
        if (is_null($count)) {
            $count = $this->count();
        }

        $records = [];
        $records["data"] = $response;
        $records["order"] = [];
        $records["draw"] = $this->params['draw'];
        $records["recordsTotal"] = $count;
        $records["recordsFiltered"] = $count;

        $http_response = new Response(json_encode($records));
        $http_response->headers->set('Content-Type', 'application/json');

        return $http_response;
    }


    public function isCalled() : bool
    {
        // $request = $this->requestStack->getCurrentRequest();
        // $is_restful_search = $request->query->get('sEcho', false);
        // return $is_restful_search;

        return true;
    }


    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function count() : mixed
    {
        $qb_count = clone($this->qb);
        $qb_count->select('COUNT('.$this->tableAs.')');

        return $qb_count->getQuery()
            ->getSingleScalarResult();
    }

}

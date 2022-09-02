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

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var QueryBuilder
     */
    private $qb;

    /**
     * @var array
     */
    private $params;

    /**
     * @var
     */
    private $tableAs;

    /**
     * @var array
     */
    private $columns;


    /**
     * Datatabler constructor.
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $em
     */
    public function __construct(RequestStack $requestStack, EntityManagerInterface $em)
    {
        //
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->columns = array();

        //
        $request = $this->requestStack->getCurrentRequest();
        $all = $request->query->all();

        $draw    = $request->query->get('draw', 0);
        $start   = $request->query->get('start', 0);
        $length  = $request->query->get('length', 50);
        $order = array_key_exists('order', $all) ? $all['order'] : [];
        $search = $all['search'];

        //
        $params = array();
        $params['draw'] = $draw;
        $params['length'] = $length;
        $params['start'] = $start;
        $params['sort_col'] = isset($order[0]) ? $order[0]['column'] : 0;
        $params['sort_dir'] = isset($order[0]) ? $order[0]['dir'] : 'ASC';
        $params['search'] = isset($search['value']) ? $search['value'] : '';
        $this->params = $params;
    }


    /**
     * @param $dataTableFields
     * @return $this
     */
    public function setColumns($dataTableFields)
    {
        //
        $this->columns = $dataTableFields;
        return $this;
    }


    public function setRepository($repoName, $dataTableFields, $repositoryMethod='getQueryDataTable')
    {
        //
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



    /**
     * @param $qb
     * @param $letter
     */
    public function setQueryBuilder($qb, $letter)
    {
        //
        $this->tableAs = $letter;
        $this->qb = $qb;
    }


    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        //
        return $this->qb;
    }


    /**
     * @return mixed
     */
    public function getLength()
    {
        //
        return $this->params['length'];
    }


    /**
     * @return mixed
     */
    public function getStart()
    {
        //
        return $this->params['start'];
    }


    /**
     * @return mixed
     */
    public function getPage()
    {
        //
        return $this->params['draw'];
    }


    /**
     * @return mixed
     */
    public function getSearch()
    {
        //
        return $this->params['search'];
    }


    /**
     * @return mixed
     */
    public function getOrderField()
    {
        //
        if (array_key_exists($this->params['sort_col'], $this->columns) && array_key_exists('field', $this->columns[$this->params['sort_col']])) {
            return $this->columns[$this->params['sort_col']]['field'];
        }
    }


    /**
     * @return mixed
     */
    public function getOrderDirection()
    {
        //
        if (array_key_exists($this->params['sort_col'], $this->columns) && array_key_exists('field', $this->columns[$this->params['sort_col']])) {
            return $this->params['sort_dir'];
        }
    }


    /**
     * @param $dataTableFields
     */
    public function setOrder($dataTableFields)
    {

        //
        if (array_key_exists($this->params['sort_col'], $dataTableFields) && array_key_exists('field', $dataTableFields[$this->params['sort_col']])) {
            $this->qb->orderBy($dataTableFields[$this->params['sort_col']]['field'], $this->params['sort_dir']);
        }


    }


    /**
     * @return mixed
     */
    public function getItems()
    {
        //
        $query = $this->qb->getQuery();

        //
        // if (strlen($this->params['search'])) {
        //     $query->setParameter('search', '%'.$this->params['search'].'%');
        // }

        //
        if ($this->params['start']) {
            $query->setFirstResult( $this->params['start'] );
        }

        //
        if ($this->params['length']) {
            $query->setMaxResults( $this->params['length'] );
        }

        //
        return $query->getResult();
    }


    /**
     * @param $response
     * @param null $count
     * @return Response
     */
    public function getResponse($response, $count=null)
    {
        //
        if (is_null($count)) {
            $count = $this->count();
        }

        //
        $records = array();
        $records["data"] = $response;
        $records["order"] = array();
        $records["draw"] = $this->params['draw'];
        $records["recordsTotal"] = $count;
        $records["recordsFiltered"] = $count;


        $http_response = new Response(json_encode($records));
        $http_response->headers->set('Content-Type', 'application/json');
        return $http_response;
    }


    /**
     * @return bool
     */
    public function isCalled()
    {
        //
        // $request = $this->requestStack->getCurrentRequest();
        //
        // $is_restful_search = $request->query->get('sEcho', false);
        // return $is_restful_search;
        return true;
    }


    /**
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function count()
    {
        $qb_count = clone($this->qb);
        $qb_count->select('COUNT('.$this->tableAs.')');

        return $qb_count->getQuery()
            ->getSingleScalarResult();
    }

}

<?php
namespace Aropixel\AdminBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;


class Datatabler
{

    protected $requestStack;
    protected $em;
    private $qb;
    private $params;
    private $tableAs;


    public function __construct(RequestStack $requestStack, EntityManagerInterface $em)
    {
        //
        $this->em = $em;
        $this->requestStack = $requestStack;

        //
        $request = $this->requestStack->getCurrentRequest();
        $draw    = $request->query->get('draw', 0);
        $start   = $request->query->get('start', 0);
        $length  = $request->query->get('length', 50);
        $order   = $request->query->get('order', array());
        $search  = $request->query->get('search', array());

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



    public function setRepository($repoName, $dataTableFields)
    {
        //
        list($bundle, $entity) = explode(':', $repoName);
        $this->tableAs = strtolower(substr($entity, 0, 1));

        $this->qb = $this->em->getRepository($repoName)->getQueryDataTable($this->params);
        $this->setOrder($dataTableFields);

        return $this;
    }



    public function setQueryBuilder($qb, $letter)
    {
        //
        $this->tableAs = $letter;
        $this->qb = $qb;
    }



    public function getQueryBuilder()
    {
        //
        return $this->qb;
    }



    public function getSearch()
    {
        //
        return $this->params['search'];
    }



    public function setOrder($dataTableFields)
    {
        //
        // $t_fields = array();
        // foreach ($dataTableFields as $i => $column) {
        //     if (array_key_exists('field', $column)) {
        //         $t_fields[] = $column['field'];
        //     }
        // }

        //
        if (array_key_exists($this->params['sort_col'], $dataTableFields) && array_key_exists('field', $dataTableFields[$this->params['sort_col']])) {
            $this->qb->orderBy($dataTableFields[$this->params['sort_col']]['field'], $this->params['sort_dir']);
        }


    }




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




    public function getResponse($response)
    {
        //
        $count = $this->count();

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


    public function isCalled()
    {
        //
        // $request = $this->requestStack->getCurrentRequest();
        //
        // $is_restful_search = $request->query->get('sEcho', false);
        // return $is_restful_search;
        return true;
    }


    public function count()
    {
        $qb_count = clone($this->qb);
        $qb_count->select('COUNT('.$this->tableAs.')');

        return $qb_count->getQuery()
            ->getSingleScalarResult();
    }

}

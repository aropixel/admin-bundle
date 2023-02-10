<?php

namespace App\Aropixel\AdminBundle\Infrastructure\Form;

use App\Aropixel\AdminBundle\Domain\Form\Select2Interface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class Select2 implements Select2Interface
{

    protected RequestStack $requestStack;
    private EntityManagerInterface $em;
    private QueryBuilder $qb;
    private mixed $query;
    private mixed $offset;
    private string $tableAs;
    private string $mode;
    private int $nbitems;


    /**
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $em
     */
    public function __construct(RequestStack $requestStack, EntityManagerInterface $em)
    {
        $this->requestStack = $requestStack;
        $this->em = $em;
        $request = $this->requestStack->getCurrentRequest();
        $this->query = $request->query->get('q', '');
        $this->offset = $request->query->get('page', 1) - 1;
        $this->mode = 'repository';
        $this->nbitems = 20;
    }


    public function setClass(string $fullNamedClass) : self
    {
        return $this->setRepository($fullNamedClass);
    }


    public function setRepository(string $fullNamedClass) : self
    {
        $reflection = new \ReflectionClass($fullNamedClass);
        $className = $reflection->getShortName();

        $this->tableAs = strtolower(substr($className, 0, 1));
        $this->qb = $this->em->getRepository($fullNamedClass)->getQuerySelect2($this->query);

        return $this;
    }


    public function getQueryBuilder()
    {
        return $this->qb;
    }


    public function getItems() : mixed
    {
        $query = $this->qb->getQuery();
        $query->setFirstResult( $this->offset * $this->nbitems );
        $query->setMaxResults( $this->nbitems );

        return $query->getResult();
    }


    public function getResponse(iterable $items) : Response
    {
        $count = $this->count();

        $t_results = array();
        $t_results["total_count"] = $count;
        $t_results["items"] = $items;

        $http_response = new Response(json_encode($t_results));
        $http_response->headers->set('Content-Type', 'application/json');

        return $http_response;
    }


    public function count() : mixed
    {
        $qb_count = clone($this->qb);
        $qb_count->select('COUNT('.$this->tableAs.')');

        return $qb_count->getQuery()
            ->getSingleScalarResult();
    }


}
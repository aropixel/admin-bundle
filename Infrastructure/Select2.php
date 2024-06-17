<?php
namespace Aropixel\AdminBundle\Infrastructure;

use Aropixel\AdminBundle\Domain\Select2Interface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class Select2 implements Select2Interface
{

    private ?QueryBuilder $qb = null;

    private string $query;

    private int $offset;
    private int $nbItems = 20;

    private ?string $tableAs = null;



    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly EntityManagerInterface $em
    )
    {
        $request = $this->requestStack->getCurrentRequest();
        $this->query = $request->query->get('q', '');
        $this->offset = $request->query->get('page', 1) - 1;
    }


    public function setClass(string $fqClassName)
    {
        return $this->setRepository($fqClassName);
    }


    public function setRepository(string $fqClassName) : Select2Interface
    {
        $reflection = new \ReflectionClass($fqClassName);
        $className = $reflection->getShortName();

        $this->tableAs = strtolower(substr($className, 0, 1));
        $this->qb = $this->em->getRepository($fqClassName)->getQuerySelect2($this->query);

        return $this;
    }

    public function getQueryBuilder() : QueryBuilder
    {
        return $this->qb;
    }

    public function getItems() : iterable
    {
        $query = $this->qb->getQuery();

        $query->setFirstResult( $this->offset * $this->nbItems );
        $query->setMaxResults( $this->nbItems );

        return $query->getResult();
    }


    public function getResponse(array $items) : Response
    {
        $count = $this->count();

        $t_results = array();
        $t_results["total_count"] = $count;
        $t_results["items"] = $items;

        $http_response = new Response(json_encode($t_results));
        $http_response->headers->set('Content-Type', 'application/json');
        return $http_response;
    }


    public function count() : int
    {
        $qb_count = clone($this->qb);
        $qb_count->select('COUNT('.$this->tableAs.')');

        return $qb_count->getQuery()
            ->getSingleScalarResult();
    }

}

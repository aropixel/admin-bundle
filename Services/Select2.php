<?php
// src/Aropixel/AdminBundle/Services/Select2.php
namespace Aropixel\AdminBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gestion des intéractions avec une liste Select2 Ajax.
 *
 * @uses https://select2.org/
 */
class Select2
{

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var
     */
    private $qb;

    /**
     * @var mixed
     */
    private $query;

    /**
     * @var int|mixed
     */
    private $offset;

    /**
     * @var
     */
    private $tableAs;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var int
     */
    private $nbitems;


    /**
     * Select2 constructor.
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $em
     */
    public function __construct(RequestStack $requestStack, EntityManagerInterface $em)
    {
        //
        $this->requestStack = $requestStack;
        $this->em = $em;

        //
        $request = $this->requestStack->getCurrentRequest();
        $this->query = $request->query->get('q', '');
        $this->offset = $request->query->get('page', 1) - 1;
        $this->mode = 'repository';

        //
        $this->nbitems = 20;
    }


    /**
     * @param $repoName
     * @return self
     */
    public function setClass($fullNamedClass)
    {
        return $this->setRepository($fullNamedClass);
    }


    /**
     * @param $repoName
     * @return self
     */
    public function setRepository($fullNamedClass)
    {
        //
        $reflection = new \ReflectionClass($fullNamedClass);
        $className = $reflection->getShortName();

        $this->tableAs = strtolower(substr($className, 0, 1));

        $this->qb = $this->em->getRepository($fullNamedClass)->getQuerySelect2($this->query);
        return $this;
    }


    /**
     * @return mixed
     */
    public function getItems()
    {
        //
        $query = $this->qb->getQuery();

        //
        $query->setFirstResult( $this->offset * $this->nbitems );
        $query->setMaxResults( $this->nbitems );

        //
        return $query->getResult();
    }


    /**
     * @param $items
     * @return Response
     */
    public function getResponse($items)
    {
        //
        $count = $this->count();

        //
        $t_results = array();
        $t_results["total_count"] = $count;
        $t_results["items"] = $items;

        //
        $http_response = new Response(json_encode($t_results));
        $http_response->headers->set('Content-Type', 'application/json');
        return $http_response;
    }


    /**
     * @return mixed
     */
    public function count()
    {
        $qb_count = clone($this->qb);
        $qb_count->select('COUNT('.$this->tableAs.')');

        return $qb_count->getQuery()
            ->getSingleScalarResult();
    }

}

<?php
// src/Aropixel/AdminBundle/Services/Select2.php
namespace Aropixel\AdminBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class Position
{

    private $em;
    private $request;


    public function __construct(RequestStack $requestStack, EntityManagerInterface $em)
    {
        //
        $this->em = $em;

        //
        $this->request = $requestStack->getCurrentRequest();

    }

    public function updatePosition($repositoryName) {

        if (!$repositoryName) {
            return new Response('KO', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        //
        $pos = 0;
        $ids = $this->request->get('entity', array());
        foreach ($ids as $id) {
            $query = $this->em->createQuery('UPDATE '.$repositoryName.' e SET e.position = :position WHERE e.id = :id')
                ->setParameter('position', $pos)
                ->setParameter('id', $id);
            $query->execute();
            $pos++;
        }

        //
        $this->em->flush();
        return new Response('OK', Response::HTTP_OK);

    }



    public function updateSinglePosition($repositoryName, $entity, $position) {

        //
        $query = $this->em->createQuery('UPDATE '.$repositoryName.' e SET e.position = :position WHERE e.id = :id')
                ->setParameter('position', $position)
                ->setParameter('id', $entity->getId());
            $query->execute();

        //
        $this->em->flush();
        return;

    }

}

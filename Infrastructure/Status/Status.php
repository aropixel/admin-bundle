<?php

namespace Aropixel\AdminBundle\Infrastructure\Status;

use Aropixel\AdminBundle\Domain\Status\StatusInterface;
use Aropixel\AdminBundle\Entity\Publishable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class Status implements StatusInterface
{

    private EntityManagerInterface $em;
    private string $property;
    private string $valueOn;
    private string $valueOff;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->property = "status";
        $this->valueOff = Publishable::STATUS_OFFLINE;
        $this->valueOn = Publishable::STATUS_ONLINE;
    }


    /**
     * Règle la propriété dont on veut gérer le statut.
     */
    public function setProperty(string $property) : self
    {
        $this->property = $property;
        return $this;
    }

    /**
     * Attribue les valeurs on et off à utiliser en statut de la propriété
     */
    public function setValues(string $valueOff, string $valueOn) : self
    {
        $this->valueOff = $valueOff;
        $this->valueOn = $valueOn;
        return $this;
    }

    /**
     * Modifie le statut d'une entité
     */
    public function changeStatus(object $entity) : Response
    {

        if (!$entity) {
            return new Response('KO', Response::HTTP_OK);
        }

        $setMethod = "set".ucfirst($this->property);
        $getMethod = "get".ucfirst($this->property);

        $statusValue = $entity->{$getMethod}() == $this->valueOff ? $this->valueOn : $this->valueOff;
        $entity->{$setMethod}($statusValue);

        $this->em->persist($entity);
        $this->em->flush();

        return new Response('OK', Response::HTTP_OK);

    }

}
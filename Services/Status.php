<?php
// src/Aropixel/AdminBundle/Services/Select2.php
namespace Aropixel\AdminBundle\Services;

use Aropixel\AdminBundle\Domain\Entity\Publishable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;


/**
 * Change le statut d'une propriété d'une classe donnée.
 *
 * @package Aropixel\AdminBundle\Services
 */
class Status
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string Propriété dont on veut changer le statut
     */
    private $property;

    /**
     * @var string Valeur off du statut
     */
    private $valueOff;

    /**
     * @var string Valeur on du statut
     */
    private $valueOn;


    /**
     * Status constructor.
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
     * Change le statut d'une entité pour la propriété réglée.
     *
     * @param $entity
     * @return Response
     */
    public function changeStatus($entity) {

        if (!$entity) {
            return new Response('KO', Response::HTTP_OK);
        }

        $setMethod = "set".ucfirst($this->property);
        $getMethod = "get".ucfirst($this->property);

        $entity->{$setMethod}($entity->{$getMethod}()==$this->valueOff ? $this->valueOn : $this->valueOff);
        $this->em->persist($entity);
        $this->em->flush();

        //
        return new Response('OK', Response::HTTP_OK);

    }

    /**
     * Règle la propriété dont on veut gérer le statut.
     *
     * @param $property string Nom de la propriété dont on veut gérer le statut
     * @return $this
     */
    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * Attribue les valeurs on et off à utiliser en statut de la propriété
     *
     * @param $valueOff string Valeur off du statut
     * @param $valueOn string Valeur on du statut
     * @return $this
     */
    public function setValues($valueOff, $valueOn)
    {
        $this->valueOff = $valueOff;
        $this->valueOn = $valueOn;

        return $this;
    }

}

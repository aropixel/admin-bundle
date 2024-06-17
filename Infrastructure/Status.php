<?php
namespace Aropixel\AdminBundle\Infrastructure;

use Aropixel\AdminBundle\Domain\StatusInterface;
use Aropixel\AdminBundle\Entity\Publishable;
use Doctrine\ORM\EntityManagerInterface;


class Status implements StatusInterface
{
    /**
     * @var string The property name, which we should change value
     */
    private string $property;

    /**
     * @var string "off" value
     */
    private string $valueOff;

    /**
     * @var string "on" value
     */
    private string $valueOn;


    public function __construct(private readonly EntityManagerInterface $em)
    {
        $this->property = "status";
        $this->valueOff = Publishable::STATUS_OFFLINE;
        $this->valueOn = Publishable::STATUS_ONLINE;
    }


    public function changeStatus(object $entity) : void {

        $setMethod = "set".ucfirst($this->property);
        $getMethod = "get".ucfirst($this->property);

        $entity->{$setMethod}($entity->{$getMethod}()==$this->valueOff ? $this->valueOn : $this->valueOff);
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function setProperty(string $property) : StatusInterface
    {
        $this->property = $property;

        return $this;
    }

    public function setValues(string $valueOff, string $valueOn) : StatusInterface
    {
        $this->valueOff = $valueOff;
        $this->valueOn = $valueOn;

        return $this;
    }

}

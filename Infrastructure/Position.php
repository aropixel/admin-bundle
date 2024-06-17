<?php
namespace Aropixel\AdminBundle\Infrastructure;

use Aropixel\AdminBundle\Domain\PositionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;


class Position implements PositionInterface
{

    public function __construct(
        protected readonly RequestStack $requestStack,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function updatePosition(string $className) : void {

        $pos = 0;
        $ids = $this->requestStack->getCurrentRequest()->get('entity', array());
        foreach ($ids as $id) {
            $query = $this->em->createQuery('UPDATE '.$className.' e SET e.position = :position WHERE e.id = :id')
                ->setParameter('position', $pos)
                ->setParameter('id', $id);
            $query->execute();
            $pos++;
        }

        $this->em->flush();
    }



    public function updateSinglePosition(string $className, int $id, int $position) : void {

        //
        $query = $this->em->createQuery('UPDATE '.$className.' e SET e.position = :position WHERE e.id = :id')
                ->setParameter('position', $position)
                ->setParameter('id', $id);
            $query->execute();

        //
        $this->em->flush();
        return;

    }

}

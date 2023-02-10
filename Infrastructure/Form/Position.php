<?php

namespace App\Aropixel\AdminBundle\Infrastructure\Form;

use App\Aropixel\AdminBundle\Domain\Form\PositionInterface;
use App\Aropixel\AdminBundle\Infrastructure\Position\EntityManagerInterface;
use App\Aropixel\AdminBundle\Infrastructure\Position\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class Position implements PositionInterface
{

    private EntityManagerInterface $em;
    private Request $request;

    /**
     * @param EntityManagerInterface $em
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $em, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
    }


    public function updatePosition(string $repositoryName) : Response
    {

        if (!$repositoryName) {
            return new Response('KO', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $pos = 0;
        $ids = $this->request->get('entity', []);

        foreach ($ids as $id) {
            $query = $this->em->createQuery('UPDATE '.$repositoryName.' e SET e.position = :position WHERE e.id = :id')
                ->setParameter('position', $pos)
                ->setParameter('id', $id);
            $query->execute();
            $pos++;
        }

        $this->em->flush();

        return new Response('OK', Response::HTTP_OK);

    }

    public function updateSinglePosition(string $repositoryName, object $entity, int $position) : void
    {

        $query = $this->em->createQuery('UPDATE '.$repositoryName.' e SET e.position = :position WHERE e.id = :id')
            ->setParameter('position', $position)
            ->setParameter('id', $entity->getId());
        $query->execute();

        $this->em->flush();

    }


}
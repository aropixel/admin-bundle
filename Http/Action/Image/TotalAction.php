<?php

namespace Aropixel\AdminBundle\Http\Action\Image;


use Aropixel\AdminBundle\Services\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TotalAction extends AbstractController
{

    private $datatableFieds = [];

    public function __construct(
        private readonly ImageManager $imageManager
    )
    {
        $this->datatableFieds = [
            ['label' => '', 'style' => 'width:50px;'],
            ['label' => '', 'style' => 'width:200px;'],
            ['field' => 'i.titre', 'label' => 'Titre'],
            ['field' => 'i.createdAt', 'label' => 'Date'],
            ['label' => '', 'style' => 'width:200px;']
        ];
    }

    /**
     * Count Image entities.
     */
    public function __invoke(Request $request) : Response
    {

        $category = $request->get('category');

        $imageClassName = $this->imageManager->getImageClassName();
        $repository = $this->entityManager->getRepository($imageClassName);
        $nbs = $repository->count($category);

        return new Response($nbs, Response::HTTP_OK);

    }


}

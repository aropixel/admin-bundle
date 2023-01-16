<?php

namespace Aropixel\AdminBundle\Http\Action\File;

use Aropixel\AdminBundle\Form\Type\File\Single\FileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class AttachAction extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ){}

    /**
     * Attach a file
     */
    public function __invoke(Request $request) : Response
    {

        $files = $request->get('files');
        $attachClass = $request->get('attach_class');
        $attachId = $request->get('attach_id');

        $t_entity = explode('\\', $attachClass);
        $entity_name = array_pop($t_entity);    array_pop($t_entity);
        $short_namespace = implode('', $t_entity);
        $attachRepositoryName = $short_namespace.':'.$entity_name;
        $em = $this->entityManager;

        if ($attachId) {
            $attachFile = $em->getRepository($attachRepositoryName)->find($attachId);
        }
        else {
            $attachFile = new $attachClass();
        }

        $html = '';
        foreach ($files as $fileId) {

            $file = $em->getRepository('AropixelAdminBundle:File')->find($fileId);
            $attachFile->setFile($file);
            $attachFile->setTitle($file->getTitre());

            $form = $this->createForm(FileType::class, $attachFile, ['data_class' => $attachClass]);

            $html.= $this->renderView('@AropixelAdmin/File/Widget/file.html.twig', [
                'form' => $form->createView()
            ]);

        }

        return new Response($html, Response::HTTP_OK);

    }


}